package com.invenco.wholesaler;

import android.app.DownloadManager;
import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import android.os.Environment;
import android.webkit.CookieManager;
import android.webkit.DownloadListener;
import android.webkit.URLUtil;
import android.webkit.WebResourceRequest;
import android.webkit.WebView;
import android.widget.Toast;
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
            webView.getSettings().setAllowFileAccess(true);
            webView.getSettings().setAllowContentAccess(true);

            // Listen for download events from links / Content-Disposition attachments
            webView.setDownloadListener(new DownloadListener() {
                @Override
                public void onDownloadStart(String url, String userAgent, String contentDisposition, String mimeType, long contentLength) {
                    handleDownload(url, userAgent, contentDisposition, mimeType);
                }
            });

            webView.setWebViewClient(new BridgeWebViewClient(getBridge()) {
                @Override
                public boolean shouldOverrideUrlLoading(WebView view, WebResourceRequest request) {
                    if (request == null || request.getUrl() == null) {
                        return false;
                    }
                    Uri url = request.getUrl();
                    String scheme = url.getScheme();

                    // If it's a PDF link or file export, trigger native download immediately!
                    if (isPdfOrExportUrl(url)) {
                        handleDownload(url.toString(), view.getSettings().getUserAgentString(), null, "application/pdf");
                        return true;
                    }

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

                    if (isPdfOrExportUrl(url)) {
                        handleDownload(urlString, view.getSettings().getUserAgentString(), null, "application/pdf");
                        return true;
                    }

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

    private boolean isPdfOrExportUrl(Uri uri) {
        if (uri == null) return false;
        String path = uri.getPath() != null ? uri.getPath().toLowerCase() : "";
        String query = uri.getQuery() != null ? uri.getQuery().toLowerCase() : "";

        return path.endsWith(".pdf") ||
               path.contains("/pdf") ||
               path.contains("/export/pdf") ||
               path.contains("/export/") ||
               query.contains("pdf=1");
    }

    private void handleDownload(String url, String userAgent, String contentDisposition, String mimeType) {
        if (url == null) return;

        try {
            Uri uri = Uri.parse(url);
            DownloadManager.Request request = new DownloadManager.Request(uri);

            String fileName = URLUtil.guessFileName(url, contentDisposition, mimeType);
            if ((mimeType != null && mimeType.toLowerCase().contains("pdf")) || url.toLowerCase().contains("pdf")) {
                if (!fileName.toLowerCase().endsWith(".pdf")) {
                    if (fileName.contains(".")) {
                        fileName = fileName.substring(0, fileName.lastIndexOf('.')) + ".pdf";
                    } else {
                        fileName = fileName + ".pdf";
                    }
                }
            }

            // Forward session cookies so authenticated Laravel routes work seamlessly without redirecting to login!
            String cookie = CookieManager.getInstance().getCookie(url);
            if (cookie != null) {
                request.addRequestHeader("Cookie", cookie);
            }
            if (userAgent != null) {
                request.addRequestHeader("User-Agent", userAgent);
            }

            request.setTitle(fileName);
            request.setDescription("Downloading " + fileName + " from Invenco...");
            request.allowScanningByMediaScanner();
            request.setNotificationVisibility(DownloadManager.Request.VISIBILITY_VISIBLE_NOTIFY_COMPLETED);
            request.setDestinationInExternalPublicDir(Environment.DIRECTORY_DOWNLOADS, fileName);

            DownloadManager dm = (DownloadManager) getSystemService(DOWNLOAD_SERVICE);
            if (dm != null) {
                dm.enqueue(request);
                Toast.makeText(this, "Downloading " + fileName + "...", Toast.LENGTH_SHORT).show();
            }
        } catch (Exception e) {
            // Fallback: If DownloadManager fails, open via system ACTION_VIEW intent
            try {
                Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(url));
                startActivity(intent);
            } catch (Exception ex) {
                Toast.makeText(this, "Download error: " + ex.getMessage(), Toast.LENGTH_LONG).show();
            }
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
