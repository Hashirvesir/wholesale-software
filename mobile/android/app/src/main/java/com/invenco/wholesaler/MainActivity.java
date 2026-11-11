package com.invenco.wholesaler;

import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import android.webkit.WebResourceRequest;
import android.webkit.WebView;
import com.getcapacitor.BridgeActivity;
import com.getcapacitor.BridgeWebViewClient;

public class MainActivity extends BridgeActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        WebView webView = getBridge() != null ? getBridge().getWebView() : null;
        if (webView != null) {
            webView.getSettings().setDomStorageEnabled(true);
            webView.getSettings().setDatabaseEnabled(true);
            webView.getSettings().setJavaScriptEnabled(true);

            webView.setWebViewClient(new BridgeWebViewClient(getBridge()) {
                @Override
                public boolean shouldOverrideUrlLoading(WebView view, WebResourceRequest request) {
                    if (request == null || request.getUrl() == null) {
                        return false;
                    }
                    Uri url = request.getUrl();
                    String scheme = url.getScheme();

                    // Keep web navigation (HTTP / HTTPS) strictly inside this app's WebView
                    if ("http".equalsIgnoreCase(scheme) || "https".equalsIgnoreCase(scheme)) {
                        return false;
                    }

                    // Launch external intent for dialer, email, WhatsApp, maps
                    try {
                        Intent intent = new Intent(Intent.ACTION_VIEW, url);
                        view.getContext().startActivity(intent);
                        return true;
                    } catch (Exception ignored) {
                        return true;
                    }
                }

                @Deprecated
                @Override
                public boolean shouldOverrideUrlLoading(WebView view, String urlString) {
                    if (urlString == null) return false;
                    Uri url = Uri.parse(urlString);
                    String scheme = url.getScheme();

                    if ("http".equalsIgnoreCase(scheme) || "https".equalsIgnoreCase(scheme)) {
                        return false;
                    }

                    try {
                        Intent intent = new Intent(Intent.ACTION_VIEW, url);
                        view.getContext().startActivity(intent);
                        return true;
                    } catch (Exception ignored) {
                        return true;
                    }
                }
            });
        }
    }

    @Override
    public void onBackPressed() {
        WebView webView = getBridge() != null ? getBridge().getWebView() : null;
        if (webView != null && webView.canGoBack()) {
            webView.goBack();
        } else {
            super.onBackPressed();
        }
    }
}
