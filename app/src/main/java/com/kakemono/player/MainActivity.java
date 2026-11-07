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
        
        // Forcer le mode plein écran permanent et garder l'écran allumé
        getWindow().setFlags(WindowManager.LayoutParams.FLAG_FULLSCREEN,
                WindowManager.LayoutParams.FLAG_FULLSCREEN);
        getWindow().addFlags(WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON);

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
        if (playlist.isEmpty()) return;

        // Récupérer l'élément actuel
        MediaItem item = playlist.get(currentIndex);

        // Passer à l'index suivant pour faire une boucle infinie
        currentIndex = (currentIndex + 1) % playlist.size();

        if ("image".equals(item.type)) {
            videoView.setVisibility(View.GONE);
            imageView.setVisibility(View.VISIBLE);

            // Téléchargement et affichage de l'image en arrière-plan
            new Thread(() -> {
                try {
                    java.io.InputStream is = new java.net.URL(item.url).openStream();
                    android.graphics.Bitmap bitmap = android.graphics.BitmapFactory.decodeStream(is);
                    runOnUiThread(() -> imageView.setImageBitmap(bitmap));
                } catch (Exception e) {
                    e.printStackTrace();
                }
            }).start();

            // Afficher l'image pendant 10 secondes avant de passer au média suivant
            handler.postDelayed(this::playNextMedia, 10000);

        } else if ("video".equals(item.type)) {
            imageView.setVisibility(View.GONE);
            videoView.setVisibility(View.VISIBLE);

            videoView.setVideoURI(Uri.parse(item.url));
            videoView.setOnPreparedListener(mp -> mp.start());
            
            // Passer au média suivant dès que la vidéo se termine
            videoView.setOnCompletionListener(mp -> playNextMedia());
            
            // En cas d'erreur de lecture, passer au suivant pour ne pas bloquer
            videoView.setOnErrorListener((mp, what, extra) -> {
                playNextMedia();
                return true;
            });
        }
    }
}