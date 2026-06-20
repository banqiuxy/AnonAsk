package com.banqiu.anonask.fragment;

import android.animation.AnimatorSet;
import android.animation.ObjectAnimator;
import android.graphics.LinearGradient;
import android.graphics.Shader;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.view.animation.AccelerateDecelerateInterpolator;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.navigation.Navigation;

import com.banqiu.anonask.R;

/**
 * 启动页 — MD3 风格 + 科技炫酷动画
 */
public class SplashFragment extends Fragment {

    private static final long SPLASH_DURATION = 2350;

    private TextView appNameText;
    private TextView taglineText;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_splash, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        appNameText = view.findViewById(R.id.splashAppName);
        taglineText = view.findViewById(R.id.splashTagline);

        // 应用渐变文字
        appNameText.post(() -> {
            if (appNameText.getMeasuredWidth() > 0) {
                Shader shader = new LinearGradient(
                        0, 0, appNameText.getMeasuredWidth(), 0,
                        new int[]{
                                getResources().getColor(R.color.gradient_start),
                                getResources().getColor(R.color.gradient_mid),
                                getResources().getColor(R.color.gradient_end)
                        },
                        null, Shader.TileMode.CLAMP);
                appNameText.getPaint().setShader(shader);
                appNameText.invalidate();
            }
        });

        // 执行动画
        playAnimation();
    }

    private void playAnimation() {
        // App Name: 从0.3缩放到1，淡入
        ObjectAnimator scaleX = ObjectAnimator.ofFloat(appNameText, "scaleX", 0.3f, 1.0f);
        ObjectAnimator scaleY = ObjectAnimator.ofFloat(appNameText, "scaleY", 0.3f, 1.0f);
        ObjectAnimator alpha = ObjectAnimator.ofFloat(appNameText, "alpha", 0f, 1f);
        scaleX.setDuration(600);
        scaleY.setDuration(600);
        alpha.setDuration(800);

        // Tagline: 延迟淡入
        ObjectAnimator taglineAlpha = ObjectAnimator.ofFloat(taglineText, "alpha", 0f, 1f);
        taglineAlpha.setDuration(500);
        taglineAlpha.setStartDelay(400);

        AnimatorSet animSet = new AnimatorSet();
        animSet.playTogether(scaleX, scaleY, alpha, taglineAlpha);
        animSet.setInterpolator(new AccelerateDecelerateInterpolator());
        animSet.start();

        // 延迟后跳转到主页面
        new Handler(Looper.getMainLooper()).postDelayed(() -> {
            if (isAdded()) {
                Navigation.findNavController(requireView())
                        .navigate(R.id.action_splash_to_main);
            }
        }, SPLASH_DURATION);
    }
}
