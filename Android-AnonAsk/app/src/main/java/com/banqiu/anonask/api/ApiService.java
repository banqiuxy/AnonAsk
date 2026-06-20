package com.banqiu.anonask.api;

import com.banqiu.anonask.model.Models;

import retrofit2.Call;
import retrofit2.http.*;

/**
 * Retrofit API 接口定义
 */
public interface ApiService {

    // ==================== 认证 ====================

    @POST("api/auth.php?action=login")
    Call<Models.ApiResponse<Models.AuthData>> login(@Body Models.LoginRequest body);

    @POST("api/auth.php?action=register")
    Call<Models.ApiResponse<Models.AuthData>> register(@Body Models.LoginRequest body);

    @POST("api/auth.php?action=logout")
    Call<Models.ApiResponse<Object>> logout();

    @GET("api/auth.php?action=check")
    Call<Models.ApiResponse<Models.CheckData>> checkLogin();

    // ==================== 问题 ====================

    @POST("api/question.php?action=create")
    Call<Models.ApiResponse<Models.QuestionCreateData>> createQuestion(@Body Models.CreateQuestionRequest body);

    @GET("api/question.php?action=list-for-user")
    Call<Models.ApiResponse<Models.ListForUserData>> listQuestionsForUser(@Query("uid") long uid);

    @GET("api/question.php?action=list-for-me")
    Call<Models.ApiResponse<Models.ListForMeData>> listQuestionsForMe(
            @Query("page") int page,
            @Query("limit") int limit,
            @Query("filter") String filter
    );

    // ==================== 回答 ====================

    @POST("api/answer.php?action=create")
    Call<Models.ApiResponse<Object>> createAnswer(@Body Models.CreateAnswerRequest body);

    @POST("api/answer.php?action=delete")
    Call<Models.ApiResponse<Object>> deleteAnswer(@Body Models.DeleteAnswerRequest body);
}
