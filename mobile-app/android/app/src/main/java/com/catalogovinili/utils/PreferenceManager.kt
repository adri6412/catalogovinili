package com.catalogovinili.utils

import android.content.Context
import android.content.SharedPreferences

object PreferenceManager {
    
    private const val PREF_NAME = "CatalogoViniliPrefs"
    private const val KEY_AUTH_TOKEN = "auth_token"
    private const val KEY_USERNAME = "username"
    
    private fun getPreferences(context: Context): SharedPreferences {
        return context.getSharedPreferences(PREF_NAME, Context.MODE_PRIVATE)
    }
    
    fun saveAuthToken(context: Context, token: String) {
        getPreferences(context).edit().putString(KEY_AUTH_TOKEN, token).apply()
    }
    
    fun getAuthToken(context: Context): String? {
        return getPreferences(context).getString(KEY_AUTH_TOKEN, null)
    }
    
    fun saveUsername(context: Context, username: String) {
        getPreferences(context).edit().putString(KEY_USERNAME, username).apply()
    }
    
    fun getUsername(context: Context): String? {
        return getPreferences(context).getString(KEY_USERNAME, null)
    }
    
    fun clearAuth(context: Context) {
        getPreferences(context).edit()
            .remove(KEY_AUTH_TOKEN)
            .remove(KEY_USERNAME)
            .apply()
    }
    
    fun isLoggedIn(context: Context): Boolean {
        return getAuthToken(context) != null
    }
}
