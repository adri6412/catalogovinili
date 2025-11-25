package com.catalogovinili.ui

import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.view.View
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import androidx.recyclerview.widget.LinearLayoutManager
import com.catalogovinili.R
import com.catalogovinili.api.RetrofitClient
import com.catalogovinili.data.EbayItem
import com.catalogovinili.databinding.ActivityEbaySearchBinding
import kotlinx.coroutines.launch

class EbaySearchActivity : AppCompatActivity() {

    private lateinit var binding: ActivityEbaySearchBinding
    private lateinit var ebayAdapter: EbayAdapter
    private val ebayItems = mutableListOf<EbayItem>()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityEbaySearchBinding.inflate(layoutInflater)
        setContentView(binding.root)
        setSupportActionBar(binding.toolbar)
        setupRecyclerView()
        binding.btnSearchEbay.setOnClickListener { performSearch() }
        
        // Check if search query was passed from another activity
        val searchQuery = intent.getStringExtra("SEARCH_QUERY")
        if (!searchQuery.isNullOrEmpty()) {
            binding.etSearchEbay.setText(searchQuery)
            performSearch()
        }
    }

    private fun setupRecyclerView() {
        ebayAdapter = EbayAdapter(ebayItems) { item ->
            // Open the eBay item URL in browser
            val intent = Intent(Intent.ACTION_VIEW, Uri.parse(item.viewItemURL))
            startActivity(intent)
        }
        binding.recyclerViewEbay.apply {
            layoutManager = LinearLayoutManager(this@EbaySearchActivity)
            adapter = ebayAdapter
        }
    }

    private fun performSearch() {
        val query = binding.etSearchEbay.text?.toString()?.trim()
        if (query.isNullOrEmpty()) {
            Toast.makeText(this, "Inserisci una ricerca", Toast.LENGTH_SHORT).show()
            return
        }
        binding.progressBar.visibility = View.VISIBLE
        lifecycleScope.launch {
            try {
                val response = RetrofitClient.apiService.searchEbay(query)
                if (response.isSuccessful && response.body()?.success == true) {
                    val data = response.body()?.data ?: emptyList()
                    ebayItems.clear()
                    ebayItems.addAll(data)
                    ebayAdapter.notifyDataSetChanged()
                } else {
                    Toast.makeText(this@EbaySearchActivity, "Errore nella ricerca", Toast.LENGTH_SHORT).show()
                }
            } catch (e: Exception) {
                Toast.makeText(this@EbaySearchActivity, "Errore: ${e.message}", Toast.LENGTH_LONG).show()
            } finally {
                binding.progressBar.visibility = View.GONE
            }
        }
    }
}
