# Catalogo Vinili - Mobile App

This directory contains the mobile application and its API server for the Catalogo Vinili project.

## Structure

```
mobile-app/
├── api-server/          # Node.js API server for mobile app
│   ├── config/          # Database configuration
│   ├── middleware/      # Authentication middleware
│   ├── routes/          # API routes (auth, vinyl, ai)
│   ├── server.js        # Main server file
│   ├── package.json     # Dependencies
│   ├── Dockerfile       # Docker configuration
│   └── .env.example     # Environment variables template
│
└── android/             # Android mobile application
    ├── app/             # Main app module
    │   ├── src/main/
    │   │   ├── java/com/catalogovinili/
    │   │   │   ├── api/         # Retrofit API client
    │   │   │   ├── data/        # Data models
    │   │   │   ├── ui/          # Activities and adapters
    │   │   │   └── utils/       # Utility classes
    │   │   ├── res/             # Resources (layouts, strings, etc.)
    │   │   └── AndroidManifest.xml
    │   └── build.gradle
    ├── build.gradle
    └── settings.gradle
```

## API Server

The API server provides RESTful endpoints for the mobile app:

### Authentication
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout
- `GET /api/auth/verify` - Verify JWT token

### Vinyl Management
- `GET /api/vinyl` - List vinyls with filters
- `GET /api/vinyl/:id` - Get single vinyl
- `POST /api/vinyl` - Add new vinyl
- `PUT /api/vinyl/:id` - Update vinyl
- `DELETE /api/vinyl/:id` - Delete vinyl
- `GET /api/vinyl/filters/artists` - Get artists list
- `GET /api/vinyl/filters/genres` - Get genres list
- `GET /api/vinyl/filters/years` - Get years list

### AI Recognition
- `POST /api/ai/recognize` - Recognize album from image
- `POST /api/ai/save` - Save recognized album

### Setup

1. **Install dependencies:**
   ```bash
   cd api-server
   npm install
   ```

2. **Configure environment variables:**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials and API keys
   ```

3. **Run locally:**
   ```bash
   npm start
   ```

4. **Run with Docker:**
   ```bash
   docker-compose up mobile-api
   ```

## Android App

The Android app is built with Kotlin and uses:
- **Retrofit** for API communication
- **Material Design 3** for UI components
- **Coroutines** for asynchronous operations
- **ViewBinding** for type-safe view access

### Features

1. **Authentication** - Login with username/password
2. **Catalog Browsing** - View all vinyls with search and filters
3. **Admin Management** - Add, edit, delete vinyls
4. **AI Recognition** - Take photo or select from gallery to recognize album
5. **eBay Integration** - Search vinyl prices on eBay (via existing API)

### Setup

1. **Open in Android Studio:**
   - Open the `android` folder in Android Studio
   - Wait for Gradle sync to complete

2. **Configure API URL:**
   - Edit `app/build.gradle`
   - Change `API_BASE_URL` to your server address
   - For emulator: `http://10.0.2.2:3002/api/`
   - For real device: `http://YOUR_SERVER_IP:3002/api/`

3. **Build and Run:**
   - Connect Android device or start emulator
   - Click Run in Android Studio

### Requirements

- Android Studio Arctic Fox or later
- Android SDK 24+ (Android 7.0+)
- Kotlin 1.9+
- Gradle 8.2+

## Docker Deployment

Both services are configured in the main `docker-compose.yml`:

```bash
# Build and start all services
docker-compose up -d

# View logs
docker-compose logs -f mobile-api

# Stop services
docker-compose down
```

## Environment Variables

### API Server (.env)

```
DB_HOST=localhost
DB_USER=catalogo
DB_PASSWORD=your_password
DB_NAME=adri641_catalogo
JWT_SECRET=your_jwt_secret_key
OPENAI_API_KEY=your_openai_api_key
PORT=3002
NODE_ENV=production
```

### Android App (build.gradle)

```gradle
buildConfigField "String", "API_BASE_URL", "\"http://YOUR_SERVER:3002/api/\""
```

## Notes

- The API server runs on port **3002** by default
- The Android app requires internet permission
- Camera and storage permissions are requested at runtime
- JWT tokens are valid for 7 days
- Images are compressed to JPEG before upload to AI endpoint
