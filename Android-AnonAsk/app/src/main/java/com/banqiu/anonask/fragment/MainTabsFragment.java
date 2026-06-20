package com.banqiu.anonask.fragment;

import android.content.SharedPreferences;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.viewpager2.adapter.FragmentStateAdapter;
import androidx.viewpager2.widget.ViewPager2;

import com.banqiu.anonask.R;
import com.google.android.material.bottomnavigation.BottomNavigationView;

/**
 * 主 Tab 容器 — 底部导航 + 左右滑动翻页
 */
public class MainTabsFragment extends Fragment {

    private ViewPager2 viewPager;
    private BottomNavigationView bottomNav;
    private long currentUid = 0;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_main_tabs, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        SharedPreferences prefs = requireActivity().getSharedPreferences("anonask", 0);
        currentUid = prefs.getLong("uid", 0);

        viewPager = view.findViewById(R.id.viewPager);
        bottomNav = view.findViewById(R.id.bottomNav);

        // ViewPager adapter — 3 页
        viewPager.setAdapter(new TabPagerAdapter(this));
        viewPager.setUserInputEnabled(true);
        viewPager.setOffscreenPageLimit(2);

        // 底部导航 + ViewPager 联动
        viewPager.registerOnPageChangeCallback(new ViewPager2.OnPageChangeCallback() {
            @Override
            public void onPageSelected(int position) {
                super.onPageSelected(position);
                int menuItemId = R.id.tab_home;
                if (position == 1) {
                    menuItemId = R.id.tab_my_link;
                } else if (position == 2) {
                    menuItemId = R.id.tab_inbox;
                }
                bottomNav.setSelectedItemId(menuItemId);
            }
        });

        bottomNav.setOnItemSelectedListener(item -> {
            int itemId = item.getItemId();
            int pageIndex;
            if (itemId == R.id.tab_home) {
                pageIndex = 0;
            } else if (itemId == R.id.tab_my_link) {
                pageIndex = 1;
            } else if (itemId == R.id.tab_inbox) {
                pageIndex = 2;
            } else {
                return false;
            }
            viewPager.setCurrentItem(pageIndex, true);
            return true;
        });

    }

    // ==================== Pager Adapter ====================

    private class TabPagerAdapter extends FragmentStateAdapter {

        TabPagerAdapter(@NonNull Fragment fragment) {
            super(fragment);
        }

        @NonNull
        @Override
        public Fragment createFragment(int position) {
            if (position == 1) {
                UserQAFragment f = new UserQAFragment();
                Bundle args = new Bundle();
                args.putLong("target_uid", currentUid);
                f.setArguments(args);
                return f;
            } else if (position == 2) {
                return new InboxFragment();
            }
            return new HomeFragment();
        }

        @Override
        public int getItemCount() {
            return 3;
        }
    }
}
