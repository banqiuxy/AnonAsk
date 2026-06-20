package com.banqiu.anonask.util;

import android.content.ClipData;
import android.content.ClipboardManager;
import android.content.Context;
import android.widget.Toast;

import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.Locale;

/**
 * 工具类 — 与 Web 端 app.js 中的工具函数对应
 */
public class Utils {

    /**
     * 将数据库时间字符串格式化为可读格式
     * 输入："2024-01-15 14:30:00" → 输出："2024-01-15 14:30"
     */
    public static String formatDate(String dateStr) {
        if (dateStr == null || dateStr.isEmpty()) return "";
        try {
            // 尝试截断到分钟
            if (dateStr.length() >= 16) {
                return dateStr.substring(0, 16);
            }
            return dateStr;
        } catch (Exception e) {
            return dateStr;
        }
    }

    /**
     * 复制文本到剪贴板
     */
    public static void copyToClipboard(Context context, String text) {
        ClipboardManager clipboard = (ClipboardManager) context.getSystemService(Context.CLIPBOARD_SERVICE);
        if (clipboard != null) {
            ClipData clip = ClipData.newPlainText("label", text);
            clipboard.setPrimaryClip(clip);
            Toast.makeText(context, "已复制到剪贴板", Toast.LENGTH_SHORT).show();
        }
    }

    /**
     * 获取当前时间的简单时间戳字符串
     */
    public static String nowString() {
        SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd HH:mm", Locale.getDefault());
        return sdf.format(new Date());
    }
}
