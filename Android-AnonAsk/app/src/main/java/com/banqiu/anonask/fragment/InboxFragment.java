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
import androidx.appcompat.app.AlertDialog;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout;

import com.banqiu.anonask.R;
import com.banqiu.anonask.api.RetrofitClient;
import com.banqiu.anonask.model.Models;
import com.banqiu.anonask.util.Utils;
import com.google.android.material.appbar.MaterialToolbar;
import com.google.android.material.button.MaterialButton;
import com.google.android.material.progressindicator.LinearProgressIndicator;
import com.google.android.material.textfield.TextInputEditText;

import java.util.ArrayList;
import java.util.List;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

/**
 * 收件箱 — 展示我收到的所有问题
 * 对应 Web: pages/dashboard.php
 */
public class InboxFragment extends Fragment {

    private RecyclerView recyclerView;
    private QuestionAdapter adapter;
    private SwipeRefreshLayout swipeRefresh;
    private LinearProgressIndicator progressBar;
    private MaterialToolbar toolbar;
    private TextView userInfoText;
    private MaterialButton copyLinkBtn;

    private View filterAll, filterPending, filterAnswered;
    private String currentFilter = "all";

    private long currentUid = 0;
    private int currentPage = 1;
    private boolean hasMore = true;
    private boolean isLoading = false;
    private List<Models.QuestionItem> questionList = new ArrayList<>();

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_inbox, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        SharedPreferences prefs = requireActivity().getSharedPreferences("anonask", 0);
        currentUid = prefs.getLong("uid", 0);

        toolbar = view.findViewById(R.id.toolbar);
        userInfoText = view.findViewById(R.id.userInfoText);
        userInfoText.setText("UID: " + currentUid + " · " + getString(R.string.inbox_subtitle));

        copyLinkBtn = view.findViewById(R.id.copyLinkBtn);
        copyLinkBtn.setOnClickListener(v -> {
            String url = com.banqiu.anonask.BuildConfig.API_BASE_URL + "u.php?uid=" + currentUid;
            Utils.copyToClipboard(requireContext(), url);
        });

        filterAll = view.findViewById(R.id.filterAll);
        filterPending = view.findViewById(R.id.filterPending);
        filterAnswered = view.findViewById(R.id.filterAnswered);

        filterAll.setOnClickListener(v -> setFilter("all", filterAll));
        filterPending.setOnClickListener(v -> setFilter("pending", filterPending));
        filterAnswered.setOnClickListener(v -> setFilter("answered", filterAnswered));

        progressBar = view.findViewById(R.id.progressBar);
        swipeRefresh = view.findViewById(R.id.swipeRefresh);
        swipeRefresh.setOnRefreshListener(() -> {
            currentPage = 1;
            loadQuestions(true);
        });

        recyclerView = view.findViewById(R.id.questionList);
        recyclerView.setLayoutManager(new LinearLayoutManager(requireContext()));
        adapter = new QuestionAdapter();
        recyclerView.setAdapter(adapter);

        recyclerView.addOnScrollListener(new RecyclerView.OnScrollListener() {
            @Override
            public void onScrolled(@NonNull RecyclerView rv, int dx, int dy) {
                super.onScrolled(rv, dx, dy);
                if (!isLoading && hasMore) {
                    LinearLayoutManager lm = (LinearLayoutManager) rv.getLayoutManager();
                    if (lm != null && lm.findLastVisibleItemPosition() >= adapter.getItemCount() - 3) {
                        loadMore();
                    }
                }
            }
        });

        // 未登录时不发起 API 请求
        if (currentUid > 0) {
            loadQuestions(false);
        }
    }

    private void setFilter(String filter, View activeTab) {
        if (currentFilter.equals(filter)) return;
        currentFilter = filter;

        filterAll.setBackgroundResource(R.drawable.filter_tab_inactive);
        filterPending.setBackgroundResource(R.drawable.filter_tab_inactive);
        filterAnswered.setBackgroundResource(R.drawable.filter_tab_inactive);
        ((TextView) filterAll).setTextColor(getResources().getColor(R.color.md_on_surface));
        ((TextView) filterPending).setTextColor(getResources().getColor(R.color.md_on_surface));
        ((TextView) filterAnswered).setTextColor(getResources().getColor(R.color.md_on_surface));

        activeTab.setBackgroundResource(R.drawable.filter_tab_active);
        ((TextView) activeTab).setTextColor(getResources().getColor(R.color.md_primary));

        currentPage = 1;
        loadQuestions(false);
    }

    private void loadQuestions(boolean isRefresh) {
        if (isLoading) return;
        isLoading = true;

        if (isRefresh) {
            swipeRefresh.setRefreshing(true);
        } else {
            progressBar.setVisibility(View.VISIBLE);
        }

        RetrofitClient.getApiService()
                .listQuestionsForMe(1, 20, currentFilter)
                .enqueue(new Callback<Models.ApiResponse<Models.ListForMeData>>() {
                    @Override
                    public void onResponse(Call<Models.ApiResponse<Models.ListForMeData>> call,
                                           Response<Models.ApiResponse<Models.ListForMeData>> response) {
                        isLoading = false;
                        progressBar.setVisibility(View.GONE);
                        swipeRefresh.setRefreshing(false);

                        if (response.body() != null && response.body().code == 0 && response.body().data != null) {
                            Models.ListForMeData data = response.body().data;
                            questionList.clear();
                            questionList.addAll(data.items);
                            hasMore = data.hasMore;
                            currentPage = data.page;
                            adapter.notifyDataSetChanged();
                        } else {
                            Toast.makeText(requireContext(), "加载失败", Toast.LENGTH_SHORT).show();
                        }
                    }

                    @Override
                    public void onFailure(Call<Models.ApiResponse<Models.ListForMeData>> call, Throwable t) {
                        isLoading = false;
                        progressBar.setVisibility(View.GONE);
                        swipeRefresh.setRefreshing(false);
                        Toast.makeText(requireContext(), "网络错误", Toast.LENGTH_SHORT).show();
                    }
                });
    }

    private void loadMore() {
        if (isLoading || !hasMore) return;
        isLoading = true;
        int nextPage = currentPage + 1;

        RetrofitClient.getApiService()
                .listQuestionsForMe(nextPage, 20, currentFilter)
                .enqueue(new Callback<Models.ApiResponse<Models.ListForMeData>>() {
                    @Override
                    public void onResponse(Call<Models.ApiResponse<Models.ListForMeData>> call,
                                           Response<Models.ApiResponse<Models.ListForMeData>> response) {
                        isLoading = false;
                        if (response.body() != null && response.body().code == 0 && response.body().data != null) {
                            Models.ListForMeData data = response.body().data;
                            questionList.addAll(data.items);
                            hasMore = data.hasMore;
                            currentPage = data.page;
                            adapter.notifyDataSetChanged();
                        }
                    }

                    @Override
                    public void onFailure(Call<Models.ApiResponse<Models.ListForMeData>> call, Throwable t) {
                        isLoading = false;
                    }
                });
    }

    private void submitAnswer(long questionId, String content, Runnable onSuccess) {
        RetrofitClient.getApiService()
                .createAnswer(new Models.CreateAnswerRequest(questionId, content))
                .enqueue(new Callback<Models.ApiResponse<Object>>() {
                    @Override
                    public void onResponse(Call<Models.ApiResponse<Object>> call,
                                           Response<Models.ApiResponse<Object>> response) {
                        if (response.body() != null && response.body().code == 0) {
                            onSuccess.run();
                        } else {
                            String msg = response.body() != null ? response.body().msg : "提交失败";
                            Toast.makeText(requireContext(), msg, Toast.LENGTH_SHORT).show();
                        }
                    }

                    @Override
                    public void onFailure(Call<Models.ApiResponse<Object>> call, Throwable t) {
                        Toast.makeText(requireContext(), "网络错误", Toast.LENGTH_SHORT).show();
                    }
                });
    }

    private void deleteQuestion(long questionId, Runnable onSuccess) {
        new AlertDialog.Builder(requireContext())
                .setTitle("确认删除")
                .setMessage(R.string.confirm_delete)
                .setPositiveButton("删除", (dialog, which) -> {
                    RetrofitClient.getApiService()
                            .deleteAnswer(new Models.DeleteAnswerRequest(questionId))
                            .enqueue(new Callback<Models.ApiResponse<Object>>() {
                                @Override
                                public void onResponse(Call<Models.ApiResponse<Object>> call,
                                                       Response<Models.ApiResponse<Object>> response) {
                                    if (response.body() != null && response.body().code == 0) {
                                        onSuccess.run();
                                    } else {
                                        String msg = response.body() != null ? response.body().msg : "删除失败";
                                        Toast.makeText(requireContext(), msg, Toast.LENGTH_SHORT).show();
                                    }
                                }

                                @Override
                                public void onFailure(Call<Models.ApiResponse<Object>> call, Throwable t) {
                                    Toast.makeText(requireContext(), "网络错误", Toast.LENGTH_SHORT).show();
                                }
                            });
                })
                .setNegativeButton("取消", null)
                .show();
    }

    // ==================== Adapter ====================

    private class QuestionAdapter extends RecyclerView.Adapter<QuestionAdapter.ViewHolder> {

        @NonNull
        @Override
        public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
            View v = LayoutInflater.from(parent.getContext())
                    .inflate(R.layout.item_inbox_question, parent, false);
            return new ViewHolder(v);
        }

        @Override
        public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
            Models.QuestionItem item = questionList.get(position);

            holder.questionContent.setText(item.getDisplayContent());
            holder.questionTime.setText(Utils.formatDate(item.getDisplayTime()));

            boolean answered = item.isAnsweredStatus();

            if (answered) {
                holder.statusTag.setText("✅ 已回答");
                holder.statusTag.setTextColor(getResources().getColor(R.color.answered_green));
                holder.actionBtn.setVisibility(View.GONE);
                holder.deleteBtn.setVisibility(View.VISIBLE);

                holder.answerArea.setVisibility(View.VISIBLE);
                holder.answerContent.setText(item.answerContent != null ? item.answerContent : "");
                holder.answerTime.setText(Utils.formatDate(item.answerTime));

                holder.deleteBtn.setOnClickListener(v ->
                        deleteQuestion(item.id, () -> {
                            int idx = questionList.indexOf(item);
                            if (idx >= 0) {
                                questionList.remove(idx);
                                notifyItemRemoved(idx);
                                notifyItemRangeChanged(idx, getItemCount());
                            }
                        })
                );
            } else {
                holder.statusTag.setText("⏳ 待回答");
                holder.statusTag.setTextColor(getResources().getColor(R.color.pending_orange));
                holder.actionBtn.setVisibility(View.VISIBLE);
                holder.actionBtn.setText(R.string.answer_btn);
                holder.deleteBtn.setVisibility(View.GONE);
                holder.answerArea.setVisibility(View.GONE);

                holder.actionBtn.setOnClickListener(v -> {
                    for (int i = 0; i < questionList.size(); i++) {
                        notifyItemChanged(i, "collapse_form");
                    }
                    holder.answerForm.setVisibility(View.VISIBLE);
                });

                holder.cancelAnswerBtn.setOnClickListener(v -> {
                    holder.answerForm.setVisibility(View.GONE);
                });

                holder.submitAnswerBtn.setOnClickListener(v -> {
                    String content = holder.answerInput.getText().toString().trim();
                    if (content.isEmpty()) {
                        Toast.makeText(requireContext(), "请输入回答内容", Toast.LENGTH_SHORT).show();
                        return;
                    }
                    holder.submitAnswerBtn.setEnabled(false);
                    holder.submitAnswerBtn.setText("提交中...");

                    submitAnswer(item.id, content, () -> {
                        Toast.makeText(requireContext(), "回答成功", Toast.LENGTH_SHORT).show();
                        currentPage = 1;
                        loadQuestions(false);
                    });
                });
            }
        }

        @Override
        public int getItemCount() {
            return questionList.size();
        }

        class ViewHolder extends RecyclerView.ViewHolder {
            TextView questionContent, questionTime, statusTag;
            TextView answerContent, answerTime, answerLabel;
            MaterialButton actionBtn, deleteBtn;
            MaterialButton cancelAnswerBtn, submitAnswerBtn;
            LinearLayout answerArea, answerForm;
            TextInputEditText answerInput;

            ViewHolder(@NonNull View itemView) {
                super(itemView);
                questionContent = itemView.findViewById(R.id.questionContent);
                questionTime = itemView.findViewById(R.id.questionTime);
                statusTag = itemView.findViewById(R.id.statusTag);
                actionBtn = itemView.findViewById(R.id.actionBtn);
                deleteBtn = itemView.findViewById(R.id.deleteBtn);
                answerArea = itemView.findViewById(R.id.answerArea);
                answerLabel = itemView.findViewById(R.id.answerLabel);
                answerContent = itemView.findViewById(R.id.answerContent);
                answerTime = itemView.findViewById(R.id.answerTime);
                answerForm = itemView.findViewById(R.id.answerForm);
                answerInput = itemView.findViewById(R.id.answerInput);
                cancelAnswerBtn = itemView.findViewById(R.id.cancelAnswerBtn);
                submitAnswerBtn = itemView.findViewById(R.id.submitAnswerBtn);
            }
        }
    }
}
