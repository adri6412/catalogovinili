package com.catalogovinili.data

data class Vinyl(
    val id: Int? = null,
    val Artista: String,
    val Titolo: String,
    val Anno: String,
    val Genere: String,
    val Supporto: String = "vinyl"
)

data class LoginRequest(
    val username: String,
    val password: String
)

data class LoginResponse(
    val success: Boolean,
    val data: LoginData?,
    val error: String?
)

data class LoginData(
    val token: String,
    val username: String
)

data class ApiResponse<T>(
    val success: Boolean,
    val data: T?,
    val error: String?,
    val message: String?,
    val count: Int?
)

data class AiRecognitionRequest(
    val image: String // Base64 encoded image
)

data class AiRecognitionData(
    val artist: String,
    val title: String,
    val year: String,
    val genre: String
)

data class VinylRequest(
    val artist: String,
    val title: String,
    val year: String,
    val genre: String,
    val support: String = "vinyl"
)

// eBay search result item
data class EbayItem(
    val title: String,
    val viewItemURL: String,
    val galleryURL: String?,
    val price: String?
)
