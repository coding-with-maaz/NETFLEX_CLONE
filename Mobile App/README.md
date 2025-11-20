# 📱 Nazaara Box Mobile App

<div align="center">

![Flutter](https://img.shields.io/badge/Flutter-3.8.1+-02569B?logo=flutter&logoColor=white)
![Dart](https://img.shields.io/badge/Dart-3.8.1+-0175C2?logo=dart&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green.svg)

[![Google Play](https://img.shields.io/badge/Google_Play-Get_It_On_Google_Play-414141?logo=google-play&logoColor=white)](https://play.google.com/store/apps/details?id=com.pro.name.generator)

**A Netflix-inspired Flutter mobile application for streaming movies and TV shows**

</div>

---

## 📖 About

**Nazaara Box Mobile App** is a cross-platform Flutter application that provides a seamless streaming experience for movies and TV shows. Built with Flutter 3.8.1+, the app offers a modern, responsive UI with smooth navigation and comprehensive content management features.

The app supports multiple platforms including Android, iOS, Web, Windows, macOS, and Linux, providing a consistent viewing experience across all devices.

## ✨ Features

- 🎥 **Rich Content Library** - Browse movies and TV shows with detailed information
- 🔍 **Advanced Search** - Find content by title, genre, category, and more
- 📱 **Cross-Platform** - Available on Android, iOS, Web, Windows, macOS, and Linux
- 🎯 **Personalized Content** - Discover trending, popular, and top-rated content
- 📺 **Episode Management** - Watch TV show episodes with season support
- 🎬 **Video Player** - Built-in video player with fullscreen support
- 💬 **User Engagement** - Request new content and track requests
- 🔔 **Push Notifications** - Get notified about new episodes and content
- 📊 **Content Categories** - Browse by genres, trending, today's releases, and more
- 🎨 **Modern UI** - Beautiful, responsive design with smooth animations
- ⚡ **Fast Performance** - Optimized with image caching and lazy loading

## 🖼️ Screenshots

<div align="center">

### App Screenshots

<img width="270" height="480" alt="Home Screen" src="https://github.com/user-attachments/assets/51253612-72eb-4077-ab2b-7a94576412a3" />
<img width="270" height="480" alt="Movies Screen" src="https://github.com/user-attachments/assets/4daa466e-457e-4f8d-917d-5693ff56863b" />
<img width="270" height="480" alt="Detail Screen" src="https://github.com/user-attachments/assets/929b1e6d-8d9f-4e99-8d15-1f3392edb3f1" />

</div>

## 🛠️ Tech Stack

### Core Technologies
- **Framework**: Flutter 3.8.1+
- **Language**: Dart 3.8.1+
- **State Management**: Provider 6.1.1
- **HTTP Client**: HTTP 1.2.0
- **Image Caching**: Cached Network Image 3.3.1

### Key Dependencies
- **UI/UX**: Shimmer effects for loading states
- **Video Player**: WebView Flutter 4.4.2 for iframe support
- **Monetization**: Google Mobile Ads 5.1.0
- **Notifications**: 
  - OneSignal Flutter 5.1.2
  - Firebase Messaging 15.1.3
- **Firebase**: Firebase Core 3.6.0
- **Storage**: SharedPreferences 2.2.2
- **Permissions**: Permission Handler 11.3.1
- **URL Launcher**: 6.2.4 for external links
- **Internationalization**: Intl 0.19.0

## 🚀 Getting Started

### Prerequisites

- Flutter SDK 3.8.1 or higher
- Dart SDK 3.8.1 or higher
- Android Studio / Xcode (for mobile development)
- VS Code or Android Studio (recommended IDE)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/coding-with-maaz/NETFLEX_CLONE.git
   cd NETFLEX_CLONE/Mobile\ App
   ```

2. **Install dependencies**
   ```bash
   flutter pub get
   ```

3. **Configure API endpoint**
   - The app connects to the Node.js backend API
   - API endpoint is configured in the app
   - Ensure the backend server is running

4. **Set up Firebase** (for push notifications)
   - Add your `google-services.json` (Android) and `GoogleService-Info.plist` (iOS)
   - Configure Firebase project in Firebase Console

5. **Configure OneSignal** (optional, for push notifications)
   - Set up OneSignal project
   - Add OneSignal App ID in the app configuration

6. **Run the app**
   ```bash
   # For Android
   flutter run

   # For iOS (macOS only)
   flutter run -d ios

   # For Web
   flutter run -d chrome

   # For specific device
   flutter devices  # List available devices
   flutter run -d <device_id>
   ```

### Build for Production

#### Android
```bash
flutter build apk --release
# or for app bundle
flutter build appbundle --release
```

#### iOS
```bash
flutter build ios --release
```

#### Web
```bash
flutter build web --release
```

#### Desktop
```bash
# Windows
flutter build windows --release

# macOS
flutter build macos --release

# Linux
flutter build linux --release
```

## 📁 Project Structure

```
Mobile App/
├── lib/
│   ├── main.dart                 # App entry point
│   ├── pages/                    # App screens
│   │   ├── home_page.dart
│   │   ├── movies_page.dart
│   │   ├── tvshows_page.dart
│   │   ├── movie_detail_page.dart
│   │   ├── tvshow_detail_page.dart
│   │   ├── trending_page.dart
│   │   ├── search_results_page.dart
│   │   └── ...
│   └── widgets/                  # Reusable widgets
│       ├── movie_card.dart
│       ├── tvshow_card.dart
│       ├── episode_card.dart
│       ├── content_row.dart
│       ├── hero_section.dart
│       ├── iframe_player.dart
│       └── ...
├── assets/
│   ├── icon.png                  # App icon
│   └── splash.png                # Splash screen image
├── android/                      # Android-specific files
├── ios/                          # iOS-specific files
├── web/                          # Web-specific files
├── windows/                      # Windows-specific files
├── macos/                        # macOS-specific files
├── linux/                        # Linux-specific files
├── test/                         # Unit and widget tests
├── pubspec.yaml                  # Dependencies and configuration
└── README.md                     # This file
```

## ⚙️ Configuration

### App Icons and Splash Screen

The app uses `flutter_launcher_icons` and `flutter_native_splash` for generating icons and splash screens:

```yaml
# Configured in pubspec.yaml
flutter_launcher_icons:
  image_path: "assets/icon.png"
  android: true
  ios: true

flutter_native_splash:
  color: "#000000"
  image: assets/splash.png
  fullscreen: true
```

To regenerate icons and splash screens:
```bash
flutter pub run flutter_launcher_icons
flutter pub run flutter_native_splash:create
```

### Environment Configuration

The app can be configured for different environments:
- Development mode
- Production mode
- Staging mode

Configure API endpoints and other settings in the app's configuration files.

## 📱 Supported Platforms

- ✅ Android
- ✅ iOS
- ✅ Web
- ✅ Windows
- ✅ macOS
- ✅ Linux

## 🧪 Testing

Run tests with:
```bash
flutter test
```

For widget tests:
```bash
flutter test test/widget_test.dart
```

## 🔧 Development

### Code Style

The project follows Flutter's recommended code style. Run:
```bash
flutter analyze
```

To format code:
```bash
flutter format .
```

### State Management

The app uses **Provider** for state management, following Flutter's recommended patterns for managing app state.

## 📦 Dependencies

Key dependencies are listed in `pubspec.yaml`. Update dependencies:
```bash
flutter pub upgrade
```

Check for outdated packages:
```bash
flutter pub outdated
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📝 License

This project is licensed under the MIT License.

## 👤 Author

**coding-with-maaz**

- GitHub: [@coding-with-maaz](https://github.com/coding-with-maaz)

## 🙏 Acknowledgments

- Inspired by Netflix's user experience
- Built with Flutter and Dart
- Thanks to all contributors and the open-source community

---

<div align="center">

**⭐ Star this repo if you find it helpful! ⭐**

Made with ❤️ by coding-with-maaz

</div>
