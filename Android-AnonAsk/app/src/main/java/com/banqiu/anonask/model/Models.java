package com.banqiu.anonask.model;

import com.google.gson.annotations.SerializedName;

import java.util.List;

/**
 * 所有数据模型 — 使用静态内部类避免 Java 每个文件一个 public 类的限制
 */
public class Models {

    // ==================== 通用 API 响应 ====================

    public static class ApiResponse<T> {
        public int code;
        public String msg;
        public T data;
    }

    // ==================== 认证 ====================

    public static class AuthData {
        public long uid;
    }

    public static class CheckData {
        public Long uid;
    }

    public static class LoginRequest {
        @SerializedName("contact_type")
        public String contactType;
        @SerializedName("contact_value")
        public String contactValue;
        public String password;

        public LoginRequest(String contactType, String contactValue, String password) {
            this.contactType = contactType;
            this.contactValue = contactValue;
            this.password = password;
        }
    }

    // ==================== 问题 ====================

    public static class CreateQuestionRequest {
        @SerializedName("target_uid")
        public long targetUid;
        public String content;

        public CreateQuestionRequest(long targetUid, String content) {
            this.targetUid = targetUid;
            this.content = content;
        }
    }

    public static class QuestionCreateData {
        @SerializedName("question_id")
        public long questionId;
    }

    /**
     * 问题条目 — 同时兼容 list-for-user 和 list-for-me 两种返回格式
     */
    public static class QuestionItem {
        public long id;
        public String content;
        @SerializedName("question_content")
        public String questionContent;
        @SerializedName("created_at")
        public String createdAt;
        @SerializedName("question_time")
        public String questionTime;
        public Integer status;
        @SerializedName("answer_content")
        public String answerContent;
        @SerializedName("answer_time")
        public String answerTime;
        @SerializedName("is_answered")
        public Boolean isAnswered;

        public String getDisplayContent() {
            return questionContent != null ? questionContent : (content != null ? content : "");
        }

        public String getDisplayTime() {
            return questionTime != null ? questionTime : (createdAt != null ? createdAt : "");
        }

        public boolean isAnsweredStatus() {
            if (isAnswered != null) return isAnswered;
            if (status != null) return status == 0;
            return answerContent != null;
        }
    }

    public static class ListForUserData {
        public List<QuestionItem> items;
    }

    public static class ListForMeData {
        public int total;
        public int page;
        public int limit;
        @SerializedName("has_more")
        public boolean hasMore;
        public List<QuestionItem> items;
    }

    // ==================== 回答 ====================

    public static class CreateAnswerRequest {
        @SerializedName("question_id")
        public long questionId;
        public String content;

        public CreateAnswerRequest(long questionId, String content) {
            this.questionId = questionId;
            this.content = content;
        }
    }

    public static class DeleteAnswerRequest {
        @SerializedName("question_id")
        public long questionId;

        public DeleteAnswerRequest(long questionId) {
            this.questionId = questionId;
        }
    }
}
