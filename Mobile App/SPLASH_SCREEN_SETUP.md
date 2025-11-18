# Splash Screen Setup Instructions

The Flutter app splash screen has been configured to use `assets/splash.png`.

## Current Configuration

- **Source Image**: `assets/splash.png`
- **Background Color**: White (#ffffff) to match the image background
- **Display Mode**: Fullscreen with fill/scaleAspectFill for optimal display
- **Platforms**: Android, iOS
- **Android 12+**: Configured with white background

## Steps to Generate Splash Screen

1. **Install the package** (if not already done):
   ```bash
   cd nazaarabox
   flutter pub get
   ```

2. **Generate splash screen assets:**
   ```bash
   flutter pub run flutter_native_splash:create
   ```

   Or use the shorter command:
   ```bash
   dart run flutter_native_splash:create
   ```

## What This Does

The `flutter_native_splash` package will:
1. Take your source image (`assets/splash.png`)
2. Generate all required sizes for each platform:
   - Android: Multiple drawable densities (mdpi, hdpi, xhdpi, xxhdpi, xxxhdpi)
   - iOS: Launch screens for all device sizes (iPhone, iPad, etc.)
3. Update native configuration files:
   - Android: `android/app/src/main/res/drawable/launch_background.xml`
   - Android 12+: `android/app/src/main/res/values-v31/styles.xml`
   - iOS: `ios/Runner/Assets.xcassets/LaunchImage.imageset/`

## Splash Screen Display

The splash screen will:
- Show immediately when the app launches
- Display the full `splash.png` image
- Use white background to match the image
- Automatically hide when Flutter engine is ready
- Work on all Android and iOS devices

## Customization

If you need to adjust the configuration, edit `pubspec.yaml` under `flutter_native_splash`:

- **`color`**: Background color (currently white)
- **`image`**: Splash image path
- **`android_gravity`**: Image positioning (fill, center, etc.)
- **`ios_content_mode`**: iOS image scaling mode

## Troubleshooting

If the splash screen doesn't update:
1. Ensure `assets/splash.png` exists
2. Clean the project: `flutter clean`
3. Run `flutter pub get`
4. Regenerate splash: `dart run flutter_native_splash:create`
5. Rebuild the app: `flutter build apk` or `flutter run`

## Testing

To test the splash screen:
- **Android**: Build and install: `flutter build apk && flutter install`
- **iOS**: Build and run: `flutter build ios && flutter run`
- Close and reopen the app to see the splash screen

## Notes

- The splash screen image should ideally be high resolution
- Recommended size: At least 1080x1920 pixels for best quality
- The image will be automatically scaled and cropped to fit different screen sizes
- Android 12+ uses a different splash screen API with white background

