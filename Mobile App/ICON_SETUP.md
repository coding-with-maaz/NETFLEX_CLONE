# App Icon Setup Instructions

The Flutter app icon has been configured to use `assets/icon.png`.

## Steps to Generate Icons

1. **Install the package:**
   ```bash
   cd nazaarabox
   flutter pub get
   ```

2. **Generate icons for all platforms:**
   ```bash
   flutter pub run flutter_launcher_icons
   ```

   Or use the shorter command:
   ```bash
   dart run flutter_launcher_icons
   ```

## Icon Requirements

- **Source Image**: `assets/icon.png`
- **Minimum Size**: 1024x1024 pixels
- **Format**: PNG with transparency support

## Platform Support

The icon will be generated for:
- ✅ Android (including adaptive icons for Android 8.0+)
- ✅ iOS
- ✅ Web
- ✅ Windows
- ✅ macOS

## Adaptive Icon (Android)

The Android adaptive icon uses:
- **Background**: Black (#000000) - matches app theme
- **Foreground**: Your icon.png image

## What This Does

The `flutter_launcher_icons` package will:
1. Take your source icon (`assets/icon.png`)
2. Generate all required sizes for each platform
3. Place them in the correct directories:
   - Android: `android/app/src/main/res/mipmap-*/`
   - iOS: `ios/Runner/Assets.xcassets/AppIcon.appiconset/`
   - Web: `web/icons/`
   - Windows: `windows/runner/resources/`
   - macOS: `macos/Runner/Assets.xcassets/AppIcon.appiconset/`

## Troubleshooting

If the icon generation fails:
1. Ensure `assets/icon.png` exists and is at least 1024x1024 pixels
2. Check that the file path is correct in `pubspec.yaml`
3. Try cleaning the project: `flutter clean`
4. Run `flutter pub get` again
5. Regenerate icons: `dart run flutter_launcher_icons`

## Verification

After generating icons, verify they were created:
- **Android**: Check `android/app/src/main/res/mipmap-*/ic_launcher.png`
- **iOS**: Check `ios/Runner/Assets.xcassets/AppIcon.appiconset/`
- **Web**: Check `web/icons/icon-*.png`

