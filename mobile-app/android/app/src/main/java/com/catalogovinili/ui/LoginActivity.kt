package com.catalogovinili.ui

import android.content.Intent
import android.os.Bundle
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import com.catalogovinili.api.RetrofitClient
import com.catalogovinili.data.LoginRequest
import com.catalogovinili.databinding.ActivityLoginBinding
import com.catalogovinili.utils.PreferenceManager
import kotlinx.coroutines.launch

class LoginActivity : AppCompatActivity() {
    
    private lateinit var binding: ActivityLoginBinding
    
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityLoginBinding.inflate(layoutInflater)
        setContentView(binding.root)
        
        // Check if already logged in
        if (PreferenceManager.isLoggedIn(this)) {
            val token = PreferenceManager.getAuthToken(this)
            RetrofitClient.setAuthToken(token)
            navigateToMain()
            return
        }
        
        setupUI()
    }
    
    private fun setupUI() {
        binding.btnLogin.setOnClickListener {
            val username = binding.etUsername.text.toString().trim()
            val password = binding.etPassword.text.toString().trim()
            
            if (username.isEmpty() || password.isEmpty()) {
                Toast.makeText(this, "Inserisci username e password", Toast.LENGTH_SHORT).show()
                return@setOnClickListener
            }
            
            performLogin(username, password)
        }
    }
    
    private fun performLogin(username: String, password: String) {
        binding.btnLogin.isEnabled = false
        binding.btnLogin.text = "Accesso in corso..."
        
        lifecycleScope.launch {
            try {
                val response = RetrofitClient.apiService.login(
                    LoginRequest(username, password)
                )
                
                if (response.isSuccessful && response.body()?.success == true) {
                    val loginData = response.body()?.data
                    if (loginData != null) {
                        // Save token and username
                        PreferenceManager.saveAuthToken(this@LoginActivity, loginData.token)
                        PreferenceManager.saveUsername(this@LoginActivity, loginData.username)
                        RetrofitClient.setAuthToken(loginData.token)
                        
                        Toast.makeText(this@LoginActivity, "Accesso effettuato", Toast.LENGTH_SHORT).show()
                        navigateToMain()
                    }
                } else {
                    val error = response.body()?.error ?: "Errore di login"
                    Toast.makeText(this@LoginActivity, error, Toast.LENGTH_LONG).show()
                    binding.btnLogin.isEnabled = true
                    binding.btnLogin.text = "Accedi"
                }
            } catch (e: Exception) {
                Toast.makeText(
                    this@LoginActivity,
                    "Errore di connessione: ${e.message}",
                    Toast.LENGTH_LONG
                ).show()
                binding.btnLogin.isEnabled = true
                binding.btnLogin.text = "Accedi"
            }
        }
    }
    
    private fun navigateToMain() {
        val intent = Intent(this, MainActivity::class.java)
        intent.flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
        startActivity(intent)
        finish()
    }
}
