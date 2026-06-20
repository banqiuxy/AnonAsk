package com.banqiu.anonask.fragment;

import android.content.SharedPreferences;
import android.os.Bundle;
import android.text.Editable;
import android.text.SpannableString;
import android.text.Spanned;
import android.text.TextPaint;
import android.text.TextWatcher;
import android.text.method.LinkMovementMethod;
import android.text.style.ClickableSpan;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.navigation.NavController;
import androidx.navigation.Navigation;

import com.banqiu.anonask.R;
import com.banqiu.anonask.api.RetrofitClient;
import com.banqiu.anonask.model.Models;
import com.google.android.material.appbar.MaterialToolbar;
import com.google.android.material.button.MaterialButton;
import com.google.android.material.textfield.TextInputEditText;
import com.google.android.material.textfield.TextInputLayout;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

/**
 * 登录/注册页
 * 对应 Web: pages/login.php
 */
public class LoginFragment extends Fragment {

    private enum Mode { LOGIN, REGISTER }
    private enum ContactType { PHONE, QQ, WECHAT }

    private Mode currentMode = Mode.LOGIN;
    private ContactType currentContactType = ContactType.PHONE;

    private View tabLogin, tabRegister;
    private View contactPhone, contactQQ, contactWechat;
    private TextInputLayout contactInputLayout;
    private TextInputEditText contactValueInput, passwordInput;
    private MaterialButton submitBtn;
    private TextView errorMsg, errorMsg2, switchHint;
    private MaterialToolbar toolbar;

    private long redirectUid = 0; // 如果 > 0，登录后跳转到该用户的公开页

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (getArguments() != null) {
            redirectUid = getArguments().getLong("redirect_target_uid", 0);
        }
    }

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_login, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        toolbar = view.findViewById(R.id.toolbar);
        tabLogin = view.findViewById(R.id.tabLogin);
        tabRegister = view.findViewById(R.id.tabRegister);
        contactPhone = view.findViewById(R.id.contactPhone);
        contactQQ = view.findViewById(R.id.contactQQ);
        contactWechat = view.findViewById(R.id.contactWechat);
        contactInputLayout = view.findViewById(R.id.contactInputLayout);
        contactValueInput = view.findViewById(R.id.contactValue);
        passwordInput = view.findViewById(R.id.password);
        submitBtn = view.findViewById(R.id.submitBtn);
        errorMsg = view.findViewById(R.id.errorMsg);
        errorMsg2 = view.findViewById(R.id.errorMsg2);
        switchHint = view.findViewById(R.id.switchHint);

        // 返回
        toolbar.setNavigationOnClickListener(v -> {
            NavController nav = Navigation.findNavController(view);
            if (nav != null) nav.navigateUp();
        });

        // Tab 点击
        tabLogin.setOnClickListener(v -> setMode(Mode.LOGIN));
        tabRegister.setOnClickListener(v -> setMode(Mode.REGISTER));

        // 联系方式点击
        contactPhone.setOnClickListener(v -> setContactType(ContactType.PHONE));
        contactQQ.setOnClickListener(v -> setContactType(ContactType.QQ));
        contactWechat.setOnClickListener(v -> setContactType(ContactType.WECHAT));

        // 提交按钮
        submitBtn.setOnClickListener(v -> performAuth());

        // 密码输入监听（实时校验提示）
        passwordInput.addTextChangedListener(new TextWatcher() {
            @Override public void beforeTextChanged(CharSequence s, int start, int count, int after) {}
            @Override public void onTextChanged(CharSequence s, int start, int before, int count) {}
            @Override public void afterTextChanged(Editable s) {
                String pwd = s.toString();
                if (!pwd.isEmpty() && !isValidPassword(pwd)) {
                    passwordInput.setError(getString(R.string.password_rule));
                } else {
                    passwordInput.setError(null);
                }
            }
        });

        setMode(Mode.LOGIN);
        updateSwitchHint();
    }

    private void setMode(Mode mode) {
        currentMode = mode;
        tabLogin.setBackgroundResource(mode == Mode.LOGIN ? R.drawable.tab_active : R.drawable.tab_inactive);
        tabRegister.setBackgroundResource(mode == Mode.REGISTER ? R.drawable.tab_active : R.drawable.tab_inactive);
        ((TextView) tabLogin).setTextColor(mode == Mode.LOGIN ?
                getResources().getColor(R.color.md_primary) :
                getResources().getColor(R.color.md_on_surface));
        ((TextView) tabRegister).setTextColor(mode == Mode.REGISTER ?
                getResources().getColor(R.color.md_primary) :
                getResources().getColor(R.color.md_on_surface));
        submitBtn.setText(mode == Mode.LOGIN ? R.string.tab_login : R.string.tab_register);
        hideError();
        updateSwitchHint();
    }

    private void setContactType(ContactType type) {
        currentContactType = type;
        contactPhone.setBackgroundResource(type == ContactType.PHONE ? R.drawable.contact_opt_active : R.drawable.contact_opt_inactive);
        contactQQ.setBackgroundResource(type == ContactType.QQ ? R.drawable.contact_opt_active : R.drawable.contact_opt_inactive);
        contactWechat.setBackgroundResource(type == ContactType.WECHAT ? R.drawable.contact_opt_active : R.drawable.contact_opt_inactive);

        ((TextView) contactPhone).setTextColor(type == ContactType.PHONE ?
                getResources().getColor(R.color.md_primary) :
                getResources().getColor(R.color.md_on_surface));
        ((TextView) contactQQ).setTextColor(type == ContactType.QQ ?
                getResources().getColor(R.color.md_primary) :
                getResources().getColor(R.color.md_on_surface));
        ((TextView) contactWechat).setTextColor(type == ContactType.WECHAT ?
                getResources().getColor(R.color.md_primary) :
                getResources().getColor(R.color.md_on_surface));

        switch (type) {
            case PHONE:
                contactInputLayout.setHint(getString(R.string.phone_hint));
                break;
            case QQ:
                contactInputLayout.setHint(getString(R.string.qq_hint));
                break;
            case WECHAT:
                contactInputLayout.setHint(getString(R.string.wechat_hint));
                break;
        }
    }

    private void updateSwitchHint() {
        String prefix = currentMode == Mode.LOGIN ?
                getString(R.string.no_account) : getString(R.string.have_account);
        String clickText = currentMode == Mode.LOGIN ?
                getString(R.string.go_register) : getString(R.string.go_login);
        String fullText = prefix + clickText;

        SpannableString ss = new SpannableString(fullText);
        ClickableSpan clickable = new ClickableSpan() {
            @Override
            public void onClick(@NonNull View widget) {
                if (currentMode == Mode.LOGIN) {
                    setMode(Mode.REGISTER);
                } else {
                    setMode(Mode.LOGIN);
                }
            }

            @Override
            public void updateDrawState(@NonNull TextPaint ds) {
                ds.setColor(getResources().getColor(R.color.md_primary));
                ds.setUnderlineText(false);
            }
        };
        ss.setSpan(clickable, prefix.length(), fullText.length(), Spanned.SPAN_EXCLUSIVE_EXCLUSIVE);

        switchHint.setText(ss);
        switchHint.setMovementMethod(LinkMovementMethod.getInstance());
    }

    private void performAuth() {
        String contactValue = contactValueInput.getText().toString().trim();
        String password = passwordInput.getText().toString().trim();

        // 校验
        if (contactValue.isEmpty()) {
            showError(getString(R.string.phone_hint) + "不能为空");
            return;
        }
        if (password.isEmpty()) {
            showError("请输入密码");
            return;
        }
        if (!isValidPassword(password)) {
            showError(getString(R.string.password_rule));
            return;
        }

        // 禁用按钮
        submitBtn.setEnabled(false);
        submitBtn.setText("处理中...");
        hideError();

        String contactTypeStr;
        switch (currentContactType) {
            case PHONE: contactTypeStr = "phone"; break;
            case QQ: contactTypeStr = "qq"; break;
            case WECHAT: contactTypeStr = "wechat"; break;
            default: contactTypeStr = "phone";
        }

        Models.LoginRequest request = new Models.LoginRequest(contactTypeStr, contactValue, password);
        Call<Models.ApiResponse<Models.AuthData>> call = (currentMode == Mode.LOGIN)
                ? RetrofitClient.getApiService().login(request)
                : RetrofitClient.getApiService().register(request);

        call.enqueue(new Callback<Models.ApiResponse<Models.AuthData>>() {
            @Override
            public void onResponse(Call<Models.ApiResponse<Models.AuthData>> call, Response<Models.ApiResponse<Models.AuthData>> response) {
                Models.ApiResponse<Models.AuthData> body = response.body();
                if (body != null && body.code == 0 && body.data != null) {
                    // 保存登录状态
                    long uid = body.data.uid;
                    SharedPreferences prefs = requireActivity().getSharedPreferences("anonask", 0);
                    prefs.edit()
                            .putBoolean("isLoggedIn", true)
                            .putLong("uid", uid)
                            .apply();

                    // 登录成功，返回主页面
                    NavController navCtrl = Navigation.findNavController(requireView());
                    if (navCtrl != null) navCtrl.navigateUp();
                } else {
                    String msg = (body != null && body.msg != null) ? body.msg : "操作失败";
                    showError(msg);
                    resetSubmitBtn();
                }
            }

            @Override
            public void onFailure(Call<Models.ApiResponse<Models.AuthData>> call, Throwable t) {
                showError("网络错误：" + t.getLocalizedMessage());
                resetSubmitBtn();
            }
        });
    }

    private boolean isValidPassword(String pwd) {
        if (pwd.length() < 6 || pwd.length() > 20) return false;
        return pwd.matches("^[a-z0-9]+$");
    }

    private void showError(String msg) {
        errorMsg.setText(msg);
        errorMsg.setVisibility(View.VISIBLE);
    }

    private void hideError() {
        errorMsg.setVisibility(View.GONE);
        errorMsg2.setVisibility(View.GONE);
    }

    private void resetSubmitBtn() {
        submitBtn.setEnabled(true);
        submitBtn.setText(currentMode == Mode.LOGIN ? R.string.tab_login : R.string.tab_register);
    }
}
