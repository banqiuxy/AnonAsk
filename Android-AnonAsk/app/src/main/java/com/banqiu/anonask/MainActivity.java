package com.banqiu.anonask;

import android.os.Build;
import android.os.Bundle;
import android.view.View;
import android.view.WindowInsetsController;



import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;
import androidx.navigation.NavController;
import androidx.navigation.fragment.NavHostFragment;

/**
 * 主 Activity — 承载 NavHostFragment
 * 使用 MD3 主题
 */
public class MainActivity extends AppCompatActivity {

    private NavController navController;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        // 系统 splash screen（MD3 风格 —— 显示图标在主题色背景上）
        androidx.core.splashscreen.SplashScreen.installSplashScreen(this);

        super.onCreate(savedInstanceState);

        // 启用边到边
        EdgeToEdge.enable(this);

        setContentView(R.layout.activity_main);

        // 强制深色状态栏图标（时间、电量、信号等）
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.R) {
            getWindow().getDecorView().getWindowInsetsController()
                    .setSystemBarsAppearance(
                            WindowInsetsController.APPEARANCE_LIGHT_STATUS_BARS,
                            WindowInsetsController.APPEARANCE_LIGHT_STATUS_BARS
                    );
        }

        // 给根容器添加内边距，让内容避开系统栏（状态栏/导航栏）
        View root = findViewById(R.id.container);
        ViewCompat.setOnApplyWindowInsetsListener(root, (v, insets) -> {
            Insets bars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(bars.left, bars.top, bars.right, bars.bottom);
            return insets;
        });

        NavHostFragment navHostFragment = (NavHostFragment) getSupportFragmentManager()
                .findFragmentById(R.id.nav_host_fragment);
        if (navHostFragment != null) {
            navController = navHostFragment.getNavController();
        }
    }
}
