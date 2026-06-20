package com.banqiu.anonask.fragment;

import android.content.SharedPreferences;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;
import android.widget.TextView;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.navigation.Navigation;
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout;

import com.banqiu.anonask.R;
import com.banqiu.anonask.api.RetrofitClient;
import com.banqiu.anonask.model.Models;
import com.banqiu.anonask.util.Utils;
import com.google.android.material.appbar.MaterialToolbar;
import com.google.android.material.button.MaterialButton;
import com.google.android.material.textfield.TextInputEditText;

import java.util.List;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

/**
 * 用户公开页 — 显示某个用户的问答列表 + 底部提问入口
 * 对应 Web: pages/u.php
 */
public class UserQAFragment extends Fragment {

    private long targetUid;
    private MaterialToolbar toolbar;
    private LinearLayout qaListContainer, emptyState;
    private LinearLayout askFormSection, ownerSection, guestSection;
    private TextInputEditText questionInput;
    private MaterialButton askBtn, goInboxBtn, loginToAskBtn;
    private SwipeRefreshLayout swipeRefresh;
    private TextView footerText;

    private boolean isLoggedIn = false;
    private long currentUid = 0;

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (getArguments() != null) {
            targetUid = getArguments().getLong("target_uid", 0);
        }
    }

    @Override
    public void onResume() {
        super.onResume();
        // 从登录页面返回后刷新状态
        if (targetUid == 0) {
            SharedPreferences prefs = requireActivity().getSharedPreferences("anonask", 0);
            long savedUid = prefs.getLong("uid", 0);
            if (savedUid != currentUid) {
                currentUid = savedUid;
                isLoggedIn = prefs.getBoolean("isLoggedIn", false);
                targetUid = savedUid;
                updateToolbarTitle();
                loadQuestions();
                // 刷新权限判断
                if (isLoggedIn && currentUid == targetUid) {
                    ownerSection.setVisibility(View.VISIBLE);
                    askFormSection.setVisibility(View.GONE);
                }
            }
        }
    }

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_user_qa, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        SharedPreferences prefs = requireActivity().getSharedPreferences("anonask", 0);
        isLoggedIn = prefs.getBoolean("isLoggedIn", false);
        currentUid = prefs.getLong("uid", 0);

        toolbar = view.findViewById(R.id.toolbar);
        toolbar.setTitleTextColor(getResources().getColor(R.color.md_primary));
        updateToolbarTitle();

        qaListContainer = view.findViewById(R.id.qaListContainer);
        emptyState = view.findViewById(R.id.emptyState);
        swipeRefresh = view.findViewById(R.id.swipeRefresh);
        footerText = view.findViewById(R.id.footerText);

        askFormSection = view.findViewById(R.id.askFormSection);
        ownerSection = view.findViewById(R.id.ownerSection);
        guestSection = view.findViewById(R.id.guestSection);
        questionInput = view.findViewById(R.id.questionInput);
        askBtn = view.findViewById(R.id.askBtn);
        goInboxBtn = view.findViewById(R.id.goInboxBtn);
        loginToAskBtn = view.findViewById(R.id.loginToAskBtn);

        // 权限判断
        if (isLoggedIn) {
            if (currentUid == targetUid) {
                ownerSection.setVisibility(View.VISIBLE);
                askFormSection.setVisibility(View.GONE);
                guestSection.setVisibility(View.GONE);
            } else {
                ownerSection.setVisibility(View.GONE);
                askFormSection.setVisibility(View.VISIBLE);
                guestSection.setVisibility(View.GONE);
            }
        } else {
            ownerSection.setVisibility(View.GONE);
            askFormSection.setVisibility(View.GONE);
            guestSection.setVisibility(View.VISIBLE);
        }

        askBtn.setOnClickListener(v -> submitQuestion());
        goInboxBtn.setOnClickListener(v -> {
            // 切换到收件箱 tab（index 2）
            if (isLoggedIn) {
                androidx.viewpager2.widget.ViewPager2 vp = requireActivity().findViewById(R.id.viewPager);
                if (vp != null) vp.setCurrentItem(2, true);
                else Navigation.findNavController(view).navigateUp();
            } else {
                Navigation.findNavController(view).navigate(R.id.loginFragment);
            }
        });
        loginToAskBtn.setOnClickListener(v -> {
            Bundle args = new Bundle();
            args.putLong("redirect_target_uid", targetUid);
            Navigation.findNavController(view).navigate(R.id.loginFragment, args);
        });

        java.util.Calendar cal = java.util.Calendar.getInstance();
        footerText.setText(getString(R.string.qa_footer)
                + "\nCopyright © " + cal.get(java.util.Calendar.YEAR) + " 半秋 & 迷雾");

        swipeRefresh.setOnRefreshListener(() -> loadQuestions());
        if (targetUid > 0) {
            loadQuestions();
        }
    }

    private void updateToolbarTitle() {
        if (toolbar != null) {
            if (targetUid > 0) {
                toolbar.setTitle("UID: " + targetUid);
            } else {
                toolbar.setTitle("未登录");
            }
        }
    }

    private void loadQuestions() {
        swipeRefresh.setRefreshing(true);

        RetrofitClient.getApiService()
                .listQuestionsForUser(targetUid)
                .enqueue(new Callback<Models.ApiResponse<Models.ListForUserData>>() {
                    @Override
                    public void onResponse(Call<Models.ApiResponse<Models.ListForUserData>> call,
                                           Response<Models.ApiResponse<Models.ListForUserData>> response) {
                        swipeRefresh.setRefreshing(false);

                        if (response.body() != null && response.body().code == 0 && response.body().data != null) {
                            List<Models.QuestionItem> items = response.body().data.items;
                            renderQuestionList(items);
                        } else {
                            Toast.makeText(requireContext(), "加载失败", Toast.LENGTH_SHORT).show();
                        }
                    }

                    @Override
                    public void onFailure(Call<Models.ApiResponse<Models.ListForUserData>> call, Throwable t) {
                        swipeRefresh.setRefreshing(false);
                        Toast.makeText(requireContext(), "网络错误", Toast.LENGTH_SHORT).show();
                    }
                });
    }

    private void renderQuestionList(List<Models.QuestionItem> items) {
        qaListContainer.removeAllViews();

        if (items == null || items.isEmpty()) {
            emptyState.setVisibility(View.VISIBLE);
            return;
        }

        emptyState.setVisibility(View.GONE);

        LayoutInflater inflater = LayoutInflater.from(requireContext());

        for (Models.QuestionItem item : items) {
            View cardView = inflater.inflate(R.layout.item_qa_pair, qaListContainer, false);

            TextView questionContent = cardView.findViewById(R.id.questionContent);
            TextView questionTime = cardView.findViewById(R.id.questionTime);
            LinearLayout answerSection = cardView.findViewById(R.id.answerSection);
            TextView answerContent = cardView.findViewById(R.id.answerContent);
            TextView answerTime = cardView.findViewById(R.id.answerTime);
            TextView pendingTag = cardView.findViewById(R.id.pendingTag);

            questionContent.setText(item.getDisplayContent());
            questionTime.setText(Utils.formatDate(item.getDisplayTime()));

            boolean isAnswered = item.isAnsweredStatus();

            if (isAnswered && item.answerContent != null) {
                answerSection.setVisibility(View.VISIBLE);
                answerContent.setText(item.answerContent);
                answerTime.setText(Utils.formatDate(item.answerTime));
                pendingTag.setVisibility(View.GONE);
            } else {
                answerSection.setVisibility(View.GONE);
                if (isLoggedIn && currentUid == targetUid) {
                    pendingTag.setVisibility(View.VISIBLE);
                } else {
                    pendingTag.setVisibility(View.GONE);
                }
            }

            qaListContainer.addView(cardView);
        }
    }

    private void submitQuestion() {
        String content = questionInput.getText().toString().trim();
        if (content.isEmpty()) {
            Toast.makeText(requireContext(), "请输入问题内容", Toast.LENGTH_SHORT).show();
            return;
        }

        askBtn.setEnabled(false);
        askBtn.setText("提交中...");

        RetrofitClient.getApiService()
                .createQuestion(new Models.CreateQuestionRequest(targetUid, content))
                .enqueue(new Callback<Models.ApiResponse<Models.QuestionCreateData>>() {
                    @Override
                    public void onResponse(Call<Models.ApiResponse<Models.QuestionCreateData>> call,
                                           Response<Models.ApiResponse<Models.QuestionCreateData>> response) {
                        askBtn.setEnabled(true);
                        askBtn.setText(R.string.submit_question);

                        if (response.body() != null && response.body().code == 0) {
                            Toast.makeText(requireContext(), "提问成功！等待对方回答。", Toast.LENGTH_SHORT).show();
                            questionInput.setText("");
                            loadQuestions();
                        } else {
                            String msg = response.body() != null ? response.body().msg : "提交失败";
                            Toast.makeText(requireContext(), msg, Toast.LENGTH_SHORT).show();
                        }
                    }

                    @Override
                    public void onFailure(Call<Models.ApiResponse<Models.QuestionCreateData>> call, Throwable t) {
                        askBtn.setEnabled(true);
                        askBtn.setText(R.string.submit_question);
                        Toast.makeText(requireContext(), "网络错误：" + t.getLocalizedMessage(), Toast.LENGTH_SHORT).show();
                    }
                });
    }
}
