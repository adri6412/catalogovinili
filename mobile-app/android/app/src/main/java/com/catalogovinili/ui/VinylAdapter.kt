package com.catalogovinili.ui

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.catalogovinili.data.Vinyl
import com.catalogovinili.databinding.ItemVinylBinding

class VinylAdapter(
    private val vinyls: List<Vinyl>,
    private val onItemClick: (Vinyl) -> Unit,
    private val onDeleteClick: (Vinyl) -> Unit
) : RecyclerView.Adapter<VinylAdapter.VinylViewHolder>() {

    inner class VinylViewHolder(private val binding: ItemVinylBinding) :
        RecyclerView.ViewHolder(binding.root) {
        fun bind(vinyl: Vinyl) {
            binding.tvArtist.text = vinyl.Artista
            binding.tvTitle.text = vinyl.Titolo
            binding.tvYear.text = vinyl.Anno
            binding.tvGenre.text = vinyl.Genere
            binding.btnDelete.setOnClickListener { onDeleteClick(vinyl) }
            binding.root.setOnClickListener { onItemClick(vinyl) }
        }
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VinylViewHolder {
        val binding = ItemVinylBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return VinylViewHolder(binding)
    }

    override fun onBindViewHolder(holder: VinylViewHolder, position: Int) {
        holder.bind(vinyls[position])
    }

    override fun getItemCount() = vinyls.size
}
