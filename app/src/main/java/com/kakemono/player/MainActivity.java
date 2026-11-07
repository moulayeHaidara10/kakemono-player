package com.kakemono.player;

import android.app.Activity;
import android.net.Uri;
import android.os.Bundle;
import android.os.Handler;
import android.view.View;
import android.view.WindowManager;
import android.widget.ImageView;
import android.widget.VideoView;

import java.util.ArrayList;
import java.util.List;

public class MainActivity extends Activity {

    private ImageView imageView;
    private VideoView videoView;
    private List<MediaItem> playlist = new ArrayList<>();
    private int currentIndex = 0;
    private Handler handler = new Handler();

    private static class MediaItem {
        String url;
        String type; // "image" ou "video"

        MediaItem(String url, String type) {
            this.url = url;
            this.type = type;
        }
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        
    setContentView(R.layout.activity_main);
    imageView = findViewById(R.id.imageView);
    videoView = findViewById(R.id.videoView);

// Test visuel immédiat
    imageView.setBackgroundColor(android.graphics.Color.RED);
    imageView.setVisibility(View.VISIBLE);

        setContentView(R.layout.activity_main);

        imageView = findViewById(R.id.imageView);
        videoView = findViewById(R.id.videoView);

        // Définir une playlist de test en dur (sans API PHP)
        playlist.add(new MediaItem("https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe", "image"));
        playlist.add(new MediaItem("https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4", "video"));

        // Lancer la lecture de la playlist
        if (!playlist.isEmpty()) {
            playNextMedia();
        }
    }

    private void playNextMedia() {
        private void playNextMedia() {
    runOnUiThread(() -> {
        // Force l'affichage d'un fond vert vif pour valider que la vue fonctionne
        imageView.setBackgroundColor(android.graphics.Color.GREEN);
        imageView.setVisibility(View.VISIBLE);
        videoView.setVisibility(View.GONE);
    });
}
}