# Nazaara Box - Mobile App

A beautiful Flutter mobile application for streaming movies and TV shows, built to match the React web frontend.

## 🎬 Quick Start

### Prerequisites
- Flutter 3.8.1+
- Production API is available at https://nazaarabox.com/api/v1

### Setup & Run

1. **Install dependencies:**
   ```bash
   flutter pub get
   ```

2. **API Configuration:**
   
   The app is configured to use the **production API** by default.
   
   Edit `lib/services/api_service.dart` if needed:
   ```dart
   // Switch between production and development
   static const bool USE_PRODUCTION = true; // Set to false for local dev
   
   // Production URL (already configured)
   static const String PRODUCTION_URL = 'https://nazaarabox.com/api/v1';
   
   // For local development, change USE_PRODUCTION to false
   // and update LOCAL_URL_ANDROID/WEB/IOS as needed
   ```

3. **Run the app:**
   ```bash
   flutter run
   ```

## ✨ Features

- 🚀 **Splash Screen** - Professional launch screen on all platforms
- 🎭 **Featured Content Carousel** - Auto-rotating hero banner
- 📺 **Latest Episodes** - Newest TV show episodes  
- 🎬 **Content Categories** - K-Drama, Movies, Anime, etc.
- ⚡ **Lazy Loading** - Efficient scrolling performance
- 🎨 **Dark Theme** - Netflix-inspired design
- 📊 **Trending Content** - Leaderboard integration
- ⭐ **TMDB Integration** - Ratings and metadata
- 🎥 **In-App Video Player** - Watch videos directly without external browser

## 📱 App Structure

```
lib/
├── main.dart                    # App entry point
├── models/                      # Data models (Movie, TVShow, Episode)
├── services/                    # API service layer
├── pages/                       # App pages (HomePage)
└── widgets/                     # Reusable components
    ├── *_card.dart             # Movie/TV/Episode cards
    ├── hero_section.dart       # Featured carousel
    ├── content_row.dart        # Horizontal lists
    ├── lazy_content_row.dart   # Lazy-loaded lists
    └── latest_episodes_row.dart # Episode list
```

## 🛠️ Development

**Hot Reload:** Press `r` in terminal  
**Hot Restart:** Press `R` in terminal  
**Quit:** Press `q`

**Build Release:**
```bash
# Android APK
flutter build apk --release

# iOS
flutter build ios --release
```

## 📖 Full Documentation

See [SETUP.md](./SETUP.md) for detailed setup instructions, troubleshooting, and development tips.

## 🔗 Backend

Production API is hosted at: `https://nazaarabox.com/api/v1`

For local development, update `USE_PRODUCTION = false` in `lib/services/api_service.dart`

## 📝 Notes

- App matches the React web frontend ([../frontend/src/pages/HomePage.jsx](../frontend/src/pages/HomePage.jsx))
- Uses the same API endpoints and data structure
- Future updates: Navigation, detail pages, search, filters

---

**Built with Flutter 💙 | Nazaara Box Project**
