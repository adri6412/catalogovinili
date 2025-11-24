# Catalogo Vinili - Mobile App Setup Guide

## Quick Start

### 1. API Server Setup

```bash
cd mobile-app/api-server
npm install
cp .env.example .env
# Edit .env with your credentials
npm start
```

The API server will run on `http://localhost:3002`

### 2. Android App Setup

1. Open `mobile-app/android` in Android Studio
2. Wait for Gradle sync
3. Edit `app/build.gradle` and set your API URL:
   - Emulator: `http://10.0.2.2:3002/api/`
   - Real device: `http://YOUR_IP:3002/api/`
4. Run the app

### 3. Docker Deployment

```bash
# From project root
docker-compose up -d mobile-api

# Check logs
docker-compose logs -f mobile-api
```

## Environment Variables

Create `.env` file in `mobile-app/api-server/`:

```env
DB_HOST=localhost
DB_USER=catalogo
DB_PASSWORD=your_password
DB_NAME=adri641_catalogo
JWT_SECRET=your_secret_key_min_32_chars
OPENAI_API_KEY=sk-...
PORT=3002
NODE_ENV=production
```

## Testing the API

### Login
```bash
curl -X POST http://localhost:3002/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"your_username","password":"your_password"}'
```

### Get Vinyls (with token)
```bash
curl http://localhost:3002/api/vinyl \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

### AI Recognition
```bash
curl -X POST http://localhost:3002/api/ai/recognize \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -F "image=@/path/to/album-cover.jpg"
```

## Troubleshooting

### API Server Issues

**Database connection failed:**
- Check DB_HOST, DB_USER, DB_PASSWORD in .env
- Ensure MySQL is running and accessible
- For Docker: use `host.docker.internal` on Windows/Mac

**OpenAI API errors:**
- Verify OPENAI_API_KEY is set correctly
- Check API key has sufficient credits
- Ensure internet connectivity

### Android App Issues

**Cannot connect to API:**
- Check API_BASE_URL in build.gradle
- For emulator, use `10.0.2.2` instead of `localhost`
- For real device, ensure phone and server are on same network
- Check firewall allows port 3002

**Permission errors:**
- Grant camera permission in app settings
- Grant storage permission in app settings
- For Android 13+, use READ_MEDIA_IMAGES permission

**Build errors:**
- Ensure Android SDK 24+ is installed
- Update Gradle if needed
- Invalidate caches and restart Android Studio

## Port Configuration

- **3000**: Existing eBay API (unchanged)
- **3002**: New Mobile API Server
- **8080/8081**: PHP Web Application (unchanged)

## Security Notes

1. **Change JWT_SECRET** in production to a strong random string (min 32 characters)
2. **Use HTTPS** in production for API communication
3. **Never commit** .env files to version control
4. **Rotate API keys** periodically
5. **Use strong passwords** for database access

## Features Implemented

### API Server
- ✅ JWT authentication
- ✅ Vinyl CRUD operations
- ✅ Search and filtering
- ✅ AI album recognition (OpenAI GPT-4 Vision)
- ✅ Database integration
- ✅ Error handling
- ✅ CORS support

### Android App
- ✅ Login/logout
- ✅ Catalog browsing with RecyclerView
- ✅ Search functionality
- ✅ Filter by artist, genre, year
- ✅ Add vinyl manually
- ✅ AI album recognition (camera/gallery)
- ✅ Material Design 3 UI
- ✅ Offline token storage

## Next Steps

1. Test the API endpoints with Postman or curl
2. Build and run the Android app
3. Test AI recognition with album covers
4. Deploy to Portainer for production use
5. Configure SSL/TLS for production API

## Support

For issues or questions, check:
- API logs: `docker-compose logs mobile-api`
- Android logcat in Android Studio
- Network requests in Android Studio Profiler
