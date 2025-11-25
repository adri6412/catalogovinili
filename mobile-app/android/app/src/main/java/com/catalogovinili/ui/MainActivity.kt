package com.catalogovinili.ui

import android.content.Intent
import android.os.Bundle
import android.view.Menu
import android.view.MenuItem
import android.view.View
import android.widget.Toast
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import androidx.recyclerview.widget.LinearLayoutManager
import com.catalogovinili.R
import com.catalogovinili.api.RetrofitClient
import com.catalogovinili.data.Vinyl
import com.catalogovinili.databinding.ActivityMainBinding
import com.catalogovinili.utils.PreferenceManager
import kotlinx.coroutines.launch

class MainActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMainBinding
    private lateinit var vinylAdapter: VinylAdapter
    private val vinyls = mutableListOf<Vinyl>()

    private var currentSearch: String? = null
    private var currentArtist: String? = null
    private var currentGenre: String? = null
    private var currentYear: String? = null

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)
        setSupportActionBar(binding.toolbar)
        setupRecyclerView()
        setupFilters()
        loadVinyls()
    }

    private fun setupRecyclerView() {
        vinylAdapter = VinylAdapter(vinyls,
            onItemClick = { vinyl -> showVinylDetails(vinyl) },
            onDeleteClick = { vinyl -> confirmDeleteVinyl(vinyl) },
            onEbaySearchClick = { vinyl -> searchVinylOnEbay(vinyl) }
        )
        binding.recyclerView.apply {
            layoutManager = LinearLayoutManager(this@MainActivity)
            adapter = vinylAdapter
        }
    }

    private fun setupFilters() {
        binding.btnSearch.setOnClickListener {
            currentSearch = binding.etSearch.text.toString().trim()
            loadVinyls()
        }
        binding.btnFilterArtist.setOnClickListener { showArtistFilter() }
        binding.btnFilterGenre.setOnClickListener { showGenreFilter() }
        binding.btnFilterYear.setOnClickListener { showYearFilter() }
        binding.btnClearFilters.setOnClickListener { clearFilters() }
    }

    private fun loadVinyls() {
        binding.progressBar.visibility = View.VISIBLE
        lifecycleScope.launch {
            try {
                val response = RetrofitClient.apiService.getVinyls(
                    search = currentSearch,
                    artist = currentArtist,
                    genre = currentGenre,
                    year = currentYear
                )
                if (response.isSuccessful && response.body()?.success == true) {
                    val data = response.body()?.data ?: emptyList()
                    vinyls.clear()
                    vinyls.addAll(data)
                    vinylAdapter.notifyDataSetChanged()
                    binding.tvCount.text = "Totale: ${data.size} dischi"
                } else {
                    Toast.makeText(this@MainActivity, "Errore nel caricamento", Toast.LENGTH_SHORT).show()
                }
            } catch (e: Exception) {
                Toast.makeText(this@MainActivity, "Errore di connessione: ${e.message}", Toast.LENGTH_LONG).show()
            } finally {
                binding.progressBar.visibility = View.GONE
            }
        }
    }

    private fun showArtistFilter() {
        lifecycleScope.launch {
            try {
                val response = RetrofitClient.apiService.getArtists()
                if (response.isSuccessful && response.body()?.success == true) {
                    val artists = response.body()?.data ?: emptyList()
                    showFilterDialog("Seleziona Artista", artists) { selected ->
                        currentArtist = selected
                        loadVinyls()
                    }
                }
            } catch (e: Exception) {
                Toast.makeText(this@MainActivity, "Errore: ${e.message}", Toast.LENGTH_SHORT).show()
            }
        }
    }

    private fun showGenreFilter() {
        lifecycleScope.launch {
            try {
                val response = RetrofitClient.apiService.getGenres()
                if (response.isSuccessful && response.body()?.success == true) {
                    val genres = response.body()?.data ?: emptyList()
                    showFilterDialog("Seleziona Genere", genres) { selected ->
                        currentGenre = selected
                        loadVinyls()
                    }
                }
            } catch (e: Exception) {
                Toast.makeText(this@MainActivity, "Errore: ${e.message}", Toast.LENGTH_SHORT).show()
            }
        }
    }

    private fun showYearFilter() {
        lifecycleScope.launch {
            try {
                val response = RetrofitClient.apiService.getYears()
                if (response.isSuccessful && response.body()?.success == true) {
                    val years = response.body()?.data ?: emptyList()
                    showFilterDialog("Seleziona Anno", years) { selected ->
                        currentYear = selected
                        loadVinyls()
                    }
                }
            } catch (e: Exception) {
                Toast.makeText(this@MainActivity, "Errore: ${e.message}", Toast.LENGTH_SHORT).show()
            }
        }
    }

    private fun showFilterDialog(title: String, items: List<String>, onSelected: (String) -> Unit) {
        AlertDialog.Builder(this)
            .setTitle(title)
            .setItems(items.toTypedArray()) { _, which -> onSelected(items[which]) }
            .setNegativeButton("Annulla", null)
            .show()
    }

    private fun clearFilters() {
        currentSearch = null
        currentArtist = null
        currentGenre = null
        currentYear = null
        binding.etSearch.text?.clear()
        loadVinyls()
    }

    private fun showVinylDetails(vinyl: Vinyl) {
        AlertDialog.Builder(this)
            .setTitle(vinyl.Titolo)
            .setMessage("""
                Artista: ${vinyl.Artista}
                Anno: ${vinyl.Anno}
                Genere: ${vinyl.Genere}
                Supporto: ${vinyl.Supporto}
            """.trimIndent())
            .setPositiveButton("OK", null)
            .show()
    }

    private fun confirmDeleteVinyl(vinyl: Vinyl) {
        AlertDialog.Builder(this)
            .setTitle("Elimina vinile")
            .setMessage("Sei sicuro di voler eliminare '${vinyl.Titolo}'?")
            .setPositiveButton("Elimina") { _, _ ->
                lifecycleScope.launch {
                    try {
                        val response = RetrofitClient.apiService.deleteVinyl(vinyl.id!!)
                        if (response.isSuccessful && response.body()?.success == true) {
                            Toast.makeText(this@MainActivity, "Vinile eliminato", Toast.LENGTH_SHORT).show()
                            loadVinyls()
                        } else {
                            Toast.makeText(this@MainActivity, "Errore eliminazione", Toast.LENGTH_SHORT).show()
                        }
                    } catch (e: Exception) {
                        Toast.makeText(this@MainActivity, "Errore: ${e.message}", Toast.LENGTH_LONG).show()
                    }
                }
            }
            .setNegativeButton("Annulla", null)
            .show()
    }

    private fun searchVinylOnEbay(vinyl: Vinyl) {
        val query = "${vinyl.Artista} ${vinyl.Titolo}"
        val intent = Intent(this, EbaySearchActivity::class.java)
        intent.putExtra("SEARCH_QUERY", query)
        startActivity(intent)
    }

    override fun onCreateOptionsMenu(menu: Menu?): Boolean {
        menuInflater.inflate(R.menu.main_menu, menu)
        return true
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        return when (item.itemId) {
            R.id.action_admin -> {
                startActivity(Intent(this, AdminActivity::class.java))
                true
            }
            R.id.action_logout -> {
                performLogout()
                true
            }
            R.id.action_search_ebay -> {
                startActivity(Intent(this, EbaySearchActivity::class.java))
                true
            }
            else -> super.onOptionsItemSelected(item)
        }
    }

    private fun performLogout() {
        PreferenceManager.clearAuth(this)
        RetrofitClient.setAuthToken(null)
        val intent = Intent(this, LoginActivity::class.java)
        intent.flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
        startActivity(intent)
        finish()
    }

    override fun onResume() {
        super.onResume()
        loadVinyls()
    }
}
