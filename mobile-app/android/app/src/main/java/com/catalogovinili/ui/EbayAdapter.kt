package com.catalogovinili.ui

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import coil.load
import com.catalogovinili.R
import com.catalogovinili.data.EbayItem
import com.catalogovinili.databinding.ItemEbayBinding

class EbayAdapter(
    private val items: List<EbayItem>,
    private val onItemClick: (EbayItem) -> Unit
) : RecyclerView.Adapter<EbayAdapter.EbayViewHolder>() {

    inner class EbayViewHolder(private val binding: ItemEbayBinding) :
        RecyclerView.ViewHolder(binding.root) {

        fun bind(item: EbayItem) {
            binding.tvTitle.text = item.title
            binding.tvPrice.text = item.price ?: "Prezzo non disponibile"

            if (!item.galleryURL.isNullOrEmpty()) {
                binding.imgEbay.load(item.galleryURL) {
                    placeholder(R.drawable.ic_launcher_foreground)
                    error(R.drawable.ic_launcher_foreground)
                }
            } else {
                binding.imgEbay.setImageResource(R.drawable.ic_launcher_foreground)
            }

            binding.root.setOnClickListener { onItemClick(item) }
        }
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): EbayViewHolder {
        val binding = ItemEbayBinding.inflate(
            LayoutInflater.from(parent.context),
            parent,
            false
        )
        return EbayViewHolder(binding)
    }

    override fun onBindViewHolder(holder: EbayViewHolder, position: Int) {
        holder.bind(items[position])
    }

    override fun getItemCount() = items.size
}
