package com.banqiu.anonask.fragment;

import android.content.SharedPreferences;
import android.graphics.LinearGradient;
import android.graphics.Shader;
import android.os.Bundle;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.navigation.NavController;
import androidx.navigation.Navigation;

import com.banqiu.anonask.R;
import com.google.android.material.appbar.MaterialToolbar;
import com.google.android.material.button.MaterialButton;

/**
 * 首页 — Hero + 三步介绍
 * 对应 Web: index.php
 */
public class HomeFragment extends Fragment {

    private static final String TAG = "HomeFragment";

    private MaterialButton btnRegisterLogin;
    private MaterialToolbar toolbar;
    private boolean isLoggedIn = false;
    private long currentUid = 0;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_home, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        try {
            checkLoginStatus();

            toolbar = view.findViewById(R.id.toolbar);
            btnRegisterLogin = view.findViewById(R.id.btnRegisterLogin);

            // Hero Title 渐变
            final TextView heroTitle = view.findViewById(R.id.heroTitle);
            if (heroTitle != null) {
                applyHeroGradient(heroTitle);
            }

            // Footer 版权
            TextView copyright = view.findViewById(R.id.copyrightText);
            if (copyright != null) {
                java.util.Calendar cal = java.util.Calendar.getInstance();
                int year = cal.get(java.util.Calendar.YEAR);
                copyright.setText("AnonAsk · 完全匿名的你问我答\nCopyright © " + year + " 半秋 & 迷雾. All rights reserved.");
            }

            // Toolbar 菜单
            if (toolbar != null) {
                toolbar.inflateMenu(R.menu.home_menu);
                toolbar.setOnMenuItemClickListener(item -> {
                    if (item.getItemId() == R.id.action_logout) {
                        performLogout();
                        return true;
                    }
                    return false;
                });
                updateMenuUI();
            }

            btnRegisterLogin.setOnClickListener(v -> navigateToLogin(0));

        } catch (Exception e) {
            Log.e(TAG, "onViewCreated crashed", e);
        }
    }

    private void checkLoginStatus() {
        SharedPreferences prefs = requireActivity().getSharedPreferences("anonask", 0);
        isLoggedIn = prefs.getBoolean("isLoggedIn", false);
        currentUid = prefs.getLong("uid", 0);
    }

    private void updateMenuUI() {
        if (toolbar == null) return;
        Menu menu = toolbar.getMenu();
        if (menu == null) return;

        MenuItem logoutItem = menu.findItem(R.id.action_logout);
        if (logoutItem != null) {
            logoutItem.setVisible(isLoggedIn);
        }

        if (btnRegisterLogin != null) {
            btnRegisterLogin.setVisibility(isLoggedIn ? View.GONE : View.VISIBLE);
        }
    }

    private void navigateToLogin(long redirectUid) {
        Bundle args = new Bundle();
        args.putLong("redirect_target_uid", redirectUid);
        NavController nav = Navigation.findNavController(requireView());
        nav.navigate(R.id.action_main_to_login, args);
    }

    private void performLogout() {
        SharedPreferences prefs = requireActivity().getSharedPreferences("anonask", 0);
        prefs.edit()
                .putBoolean("isLoggedIn", false)
                .putLong("uid", 0)
                .apply();

        com.banqiu.anonask.api.RetrofitClient.getApiService().logout()
                .enqueue(new retrofit2.Callback<com.banqiu.anonask.model.Models.ApiResponse<Object>>() {
                    @Override
                    public void onResponse(retrofit2.Call<com.banqiu.anonask.model.Models.ApiResponse<Object>> call,
                                           retrofit2.Response<com.banqiu.anonask.model.Models.ApiResponse<Object>> response) {
                        isLoggedIn = false;
                        currentUid = 0;
                        updateMenuUI();
                    }

                    @Override
                    public void onFailure(retrofit2.Call<com.banqiu.anonask.model.Models.ApiResponse<Object>> call, Throwable t) {
                        isLoggedIn = false;
                        currentUid = 0;
                        updateMenuUI();
                    }
                });
    }

    private void applyHeroGradient(@NonNull final TextView heroTitle) {
        heroTitle.getViewTreeObserver().addOnGlobalLayoutListener(
                new android.view.ViewTreeObserver.OnGlobalLayoutListener() {
                    @Override
                    public void onGlobalLayout() {
                        if (heroTitle.getMeasuredWidth() <= 0) return;
                        heroTitle.getViewTreeObserver().removeOnGlobalLayoutListener(this);
                        applyShader(heroTitle);
                    }
                }
        );
        // 如果布局已完成，直接应用
        heroTitle.post(() -> {
            if (heroTitle.getMeasuredWidth() > 0) {
                applyShader(heroTitle);
            }
        });
    }

    private void applyShader(TextView heroTitle) {
        try {
            Shader textShader = new LinearGradient(
                    0, 0, heroTitle.getMeasuredWidth(), 0,
                    new int[]{
                            requireContext().getColor(R.color.gradient_start),
                            requireContext().getColor(R.color.gradient_mid),
                            requireContext().getColor(R.color.gradient_end)
                    },
                    null, Shader.TileMode.CLAMP);
            heroTitle.getPaint().setShader(textShader);
            heroTitle.invalidate(); // 强制重绘
        } catch (Exception e) {
            Log.w(TAG, "Hero gradient failed", e);
        }
    }

    @Override
    public void onResume() {
        super.onResume();
        checkLoginStatus();
        updateMenuUI();
        // 每次恢复时重新应用渐变（修复偶现灰色）
        View v = getView();
        if (v != null) {
            TextView heroTitle = v.findViewById(R.id.heroTitle);
            if (heroTitle != null) {
                applyShader(heroTitle);
            }
        }
    }
}
