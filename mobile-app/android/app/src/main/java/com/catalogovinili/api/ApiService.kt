package com.catalogovinili.api

import com.catalogovinili.data.*
import okhttp3.MultipartBody
import retrofit2.Response
import retrofit2.http.*

interface ApiService {

    // Authentication
    @POST("auth/login")
    suspend fun login(@Body request: LoginRequest): Response<LoginResponse>

    @POST("auth/logout")
    suspend fun logout(): Response<ApiResponse<Unit>>

    @GET("auth/verify")
    suspend fun verifyToken(): Response<ApiResponse<LoginData>>

    // Vinyl CRUD
    @GET("vinyl")
    suspend fun getVinyls(
        @Query("search") search: String? = null,
        @Query("artist") artist: String? = null,
        @Query("genre") genre: String? = null,
        @Query("year") year: String? = null
    ): Response<ApiResponse<List<Vinyl>>>

    @GET("vinyl/{id}")
    suspend fun getVinyl(@Path("id") id: Int): Response<ApiResponse<Vinyl>>

    @POST("vinyl")
    suspend fun addVinyl(@Body vinyl: VinylRequest): Response<ApiResponse<Vinyl>>

    @PUT("vinyl/{id}")
    suspend fun updateVinyl(
        @Path("id") id: Int,
        @Body vinyl: VinylRequest
    ): Response<ApiResponse<Vinyl>>

    @DELETE("vinyl/{id}")
    suspend fun deleteVinyl(@Path("id") id: Int): Response<ApiResponse<Unit>>

    // Filters
    @GET("vinyl/filters/artists")
    suspend fun getArtists(): Response<ApiResponse<List<String>>>

    @GET("vinyl/filters/genres")
    suspend fun getGenres(): Response<ApiResponse<List<String>>>

    @GET("vinyl/filters/years")
    suspend fun getYears(): Response<ApiResponse<List<String>>>

    // AI Recognition
    @Multipart
    @POST("ai/recognize")
    suspend fun recognizeAlbum(
        @Part image: MultipartBody.Part
    ): Response<ApiResponse<AiRecognitionData>>

    @POST("ai/save")
    suspend fun saveRecognizedAlbum(@Body vinyl: VinylRequest): Response<ApiResponse<Vinyl>>

    // eBay Search
    @GET("ebay/search")
    suspend fun searchEbay(@Query("q") query: String): Response<ApiResponse<List<EbayItem>>>
}
