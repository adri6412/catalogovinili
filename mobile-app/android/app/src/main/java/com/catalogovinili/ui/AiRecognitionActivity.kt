package com.catalogovinili.ui

import android.Manifest
import android.content.Intent
import android.content.pm.PackageManager
import android.graphics.Bitmap
import android.net.Uri
import android.os.Bundle
import android.provider.MediaStore
import android.view.View
import android.widget.Toast
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.ContextCompat
import androidx.lifecycle.lifecycleScope
import com.catalogovinili.api.RetrofitClient
import com.catalogovinili.data.VinylRequest
import com.catalogovinili.databinding.ActivityAiRecognitionBinding
import kotlinx.coroutines.launch
import okhttp3.MediaType.Companion.toMediaTypeOrNull
import okhttp3.MultipartBody
import okhttp3.RequestBody.Companion.toRequestBody
import java.io.ByteArrayOutputStream

class AiRecognitionActivity : AppCompatActivity() {
    
    private lateinit var binding: ActivityAiRecognitionBinding
    private var selectedImageBitmap: Bitmap? = null
    
    private val cameraPermissionLauncher = registerForActivityResult(
        ActivityResultContracts.RequestPermission()
    ) { isGranted ->
        if (isGranted) {
            openCamera()
        } else {
            Toast.makeText(this, "Permesso fotocamera negato", Toast.LENGTH_SHORT).show()
        }
    }
    
    private val galleryPermissionLauncher = registerForActivityResult(
        ActivityResultContracts.RequestPermission()
    ) { isGranted ->
        if (isGranted) {
            openGallery()
        } else {
            Toast.makeText(this, "Permesso galleria negato", Toast.LENGTH_SHORT).show()
        }
    }
    
    private val cameraLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (result.resultCode == RESULT_OK) {
            val bitmap = result.data?.extras?.get("data") as? Bitmap
            if (bitmap != null) {
                selectedImageBitmap = bitmap
                binding.ivAlbumCover.setImageBitmap(bitmap)
                binding.ivAlbumCover.visibility = View.VISIBLE
            }
        }
    }
    
    private val galleryLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (result.resultCode == RESULT_OK) {
            val uri = result.data?.data
            if (uri != null) {
                try {
                    val bitmap = MediaStore.Images.Media.getBitmap(contentResolver, uri)
                    selectedImageBitmap = bitmap
                    binding.ivAlbumCover.setImageBitmap(bitmap)
                    binding.ivAlbumCover.visibility = View.VISIBLE
                } catch (e: Exception) {
                    Toast.makeText(this, "Errore nel caricamento dell'immagine", Toast.LENGTH_SHORT).show()
                }
            }
        }
    }
    
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityAiRecognitionBinding.inflate(layoutInflater)
        setContentView(binding.root)
        
        supportActionBar?.setDisplayHomeAsUpEnabled(true)
        supportActionBar?.title = "Riconoscimento AI"
        
        setupUI()
    }
    
    private fun setupUI() {
        binding.btnCamera.setOnClickListener {
            checkCameraPermission()
        }
        
        binding.btnGallery.setOnClickListener {
            checkGalleryPermission()
        }
        
        binding.btnAnalyze.setOnClickListener {
            analyzeImage()
        }
    }
    
    private fun checkCameraPermission() {
        when {
            ContextCompat.checkSelfPermission(
                this,
                Manifest.permission.CAMERA
            ) == PackageManager.PERMISSION_GRANTED -> {
                openCamera()
            }
            else -> {
                cameraPermissionLauncher.launch(Manifest.permission.CAMERA)
            }
        }
    }
    
    private fun checkGalleryPermission() {
        val permission = if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.TIRAMISU) {
            Manifest.permission.READ_MEDIA_IMAGES
        } else {
            Manifest.permission.READ_EXTERNAL_STORAGE
        }
        
        when {
            ContextCompat.checkSelfPermission(this, permission) == PackageManager.PERMISSION_GRANTED -> {
                openGallery()
            }
            else -> {
                galleryPermissionLauncher.launch(permission)
            }
        }
    }
    
    private fun openCamera() {
        val intent = Intent(MediaStore.ACTION_IMAGE_CAPTURE)
        cameraLauncher.launch(intent)
    }
    
    private fun openGallery() {
        val intent = Intent(Intent.ACTION_PICK, MediaStore.Images.Media.EXTERNAL_CONTENT_URI)
        galleryLauncher.launch(intent)
    }
    
    private fun analyzeImage() {
        val bitmap = selectedImageBitmap
        if (bitmap == null) {
            Toast.makeText(this, "Seleziona prima un'immagine", Toast.LENGTH_SHORT).show()
            return
        }
        
        binding.progressBar.visibility = View.VISIBLE
        binding.btnAnalyze.isEnabled = false
        binding.btnAnalyze.text = "Elaborazione in corso..."
        
        lifecycleScope.launch {
            try {
                // Convert bitmap to byte array
                val stream = ByteArrayOutputStream()
                bitmap.compress(Bitmap.CompressFormat.JPEG, 90, stream)
                val byteArray = stream.toByteArray()
                
                // Create multipart request
                val requestBody = byteArray.toRequestBody("image/jpeg".toMediaTypeOrNull())
                val imagePart = MultipartBody.Part.createFormData("image", "album.jpg", requestBody)
                
                // Call API
                val response = RetrofitClient.apiService.recognizeAlbum(imagePart)
                
                if (response.isSuccessful && response.body()?.success == true) {
                    val data = response.body()?.data
                    if (data != null) {
                        showRecognitionResult(data.artist, data.title, data.year, data.genre)
                    }
                } else {
                    val error = response.body()?.error ?: "Errore nel riconoscimento"
                    Toast.makeText(this@AiRecognitionActivity, error, Toast.LENGTH_LONG).show()
                }
            } catch (e: Exception) {
                Toast.makeText(
                    this@AiRecognitionActivity,
                    "Errore: ${e.message}",
                    Toast.LENGTH_LONG
                ).show()
            } finally {
                binding.progressBar.visibility = View.GONE
                binding.btnAnalyze.isEnabled = true
                binding.btnAnalyze.text = "Analizza con AI"
            }
        }
    }
    
    private fun showRecognitionResult(artist: String, title: String, year: String, genre: String) {
        val message = """
            Artista: $artist
            Titolo: $title
            Anno: $year
            Genere: $genre
        """.trimIndent()
        
        AlertDialog.Builder(this)
            .setTitle("Dati Estratti")
            .setMessage(message)
            .setPositiveButton("Salva nel Database") { _, _ ->
                saveToDatabase(artist, title, year, genre)
            }
            .setNegativeButton("Annulla", null)
            .show()
    }
    
    private fun saveToDatabase(artist: String, title: String, year: String, genre: String) {
        lifecycleScope.launch {
            try {
                val response = RetrofitClient.apiService.addVinyl(
                    VinylRequest(
                        artist = artist,
                        title = title,
                        year = year,
                        genre = genre,
                        support = "vinyl"
                    )
                )
                
                if (response.isSuccessful && response.body()?.success == true) {
                    Toast.makeText(
                        this@AiRecognitionActivity,
                        "Vinile salvato con successo",
                        Toast.LENGTH_SHORT
                    ).show()
                    finish()
                } else {
                    Toast.makeText(
                        this@AiRecognitionActivity,
                        "Errore nel salvataggio",
                        Toast.LENGTH_SHORT
                    ).show()
                }
            } catch (e: Exception) {
                Toast.makeText(
                    this@AiRecognitionActivity,
                    "Errore: ${e.message}",
                    Toast.LENGTH_LONG
                ).show()
            }
        }
    }
    
    override fun onSupportNavigateUp(): Boolean {
        finish()
        return true
    }
}
