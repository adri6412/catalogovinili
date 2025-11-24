package com.catalogovinili.ui

import android.content.Intent
import android.os.Bundle
import android.widget.Toast
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import com.catalogovinili.api.RetrofitClient
import com.catalogovinili.data.VinylRequest
import com.catalogovinili.databinding.ActivityAdminBinding
import kotlinx.coroutines.launch

class AdminActivity : AppCompatActivity() {
    
    private lateinit var binding: ActivityAdminBinding
    
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityAdminBinding.inflate(layoutInflater)
        setContentView(binding.root)
        
        supportActionBar?.setDisplayHomeAsUpEnabled(true)
        supportActionBar?.title = "Gestione Vinili"
        
        setupUI()
    }
    
    private fun setupUI() {
        binding.btnAddVinyl.setOnClickListener {
            addVinyl()
        }
        
        binding.btnAiRecognition.setOnClickListener {
            startActivity(Intent(this, AiRecognitionActivity::class.java))
        }
    }
    
    private fun addVinyl() {
        val artist = binding.etArtist.text.toString().trim()
        val title = binding.etTitle.text.toString().trim()
        val year = binding.etYear.text.toString().trim()
        val genre = binding.etGenre.text.toString().trim()
        val support = binding.etSupport.text.toString().trim().ifEmpty { "vinyl" }
        
        if (artist.isEmpty() || title.isEmpty()) {
            Toast.makeText(this, "Artista e Titolo sono obbligatori", Toast.LENGTH_SHORT).show()
            return
        }
        
        binding.btnAddVinyl.isEnabled = false
        binding.btnAddVinyl.text = "Aggiunta in corso..."
        
        lifecycleScope.launch {
            try {
                val response = RetrofitClient.apiService.addVinyl(
                    VinylRequest(
                        artist = artist,
                        title = title,
                        year = year,
                        genre = genre,
                        support = support
                    )
                )
                
                if (response.isSuccessful && response.body()?.success == true) {
                    Toast.makeText(
                        this@AdminActivity,
                        "Vinile aggiunto con successo",
                        Toast.LENGTH_SHORT
                    ).show()
                    clearForm()
                } else {
                    val error = response.body()?.error ?: "Errore durante l'aggiunta"
                    Toast.makeText(this@AdminActivity, error, Toast.LENGTH_LONG).show()
                }
            } catch (e: Exception) {
                Toast.makeText(
                    this@AdminActivity,
                    "Errore di connessione: ${e.message}",
                    Toast.LENGTH_LONG
                ).show()
            } finally {
                binding.btnAddVinyl.isEnabled = true
                binding.btnAddVinyl.text = "Aggiungi Vinile"
            }
        }
    }
    
    private fun clearForm() {
        binding.etArtist.text.clear()
        binding.etTitle.text.clear()
        binding.etYear.text.clear()
        binding.etGenre.text.clear()
        binding.etSupport.text.clear()
    }
    
    override fun onSupportNavigateUp(): Boolean {
        finish()
        return true
    }
}
