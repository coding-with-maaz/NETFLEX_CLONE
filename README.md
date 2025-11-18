# 🎬 Nazaara Box

<div align="center">

![Banner](https://github.com/user-attachments/assets/c5e4537c-d31e-4d38-a65f-15344042e1d2)

**A Netflix-inspired streaming platform for movies and TV shows**

[![Flutter](https://img.shields.io/badge/Flutter-3.8.1+-02569B?logo=flutter&logoColor=white)](https://flutter.dev)
[![Node.js](https://img.shields.io/badge/Node.js-18+-339933?logo=node.js&logoColor=white)](https://nodejs.org)
[![Express](https://img.shields.io/badge/Express-4.19.2-000000?logo=express&logoColor=white)](https://expressjs.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

</div>

---

## 📖 About

**Nazaara Box** is a full-stack streaming platform that provides a seamless Netflix-like experience for watching movies and TV shows. The platform consists of a Flutter mobile application and a Node.js/Express backend API, offering comprehensive content management, search functionality, and user engagement features.

## ✨ Features

- 🎥 **Rich Content Library** - Browse movies and TV shows with detailed information
- 🔍 **Advanced Search** - Find content by title, genre, category, and more
- 📱 **Cross-Platform** - Available on Android, iOS, Web, Windows, macOS, and Linux
- 🎯 **Personalized Recommendations** - Discover trending, popular, and top-rated content
- 📺 **Episode Management** - Track and watch TV show episodes with season support
- 💬 **User Engagement** - Comments, requests, and reporting features
- 🏆 **Leaderboard** - Track user activity and engagement
- 📊 **Analytics** - Comprehensive content and user analytics

## 🖼️ Screenshots

<div align="center">

### Mobile App Screenshots

<img width="270" height="480" alt="ScreenShot_App (1)" src="https://github.com/user-attachments/assets/51253612-72eb-4077-ab2b-7a94576412a3" />
<img width="270" height="480" alt="ScreenShot_App (2)" src="https://github.com/user-attachments/assets/4daa466e-457e-4f8d-917d-5693ff56863b" />
<img width="270" height="480" alt="ScreenShot_App (3)" src="https://github.com/user-attachments/assets/929b1e6d-8d9f-4e99-8d15-1f3392edb3f1" />
<img width="270" height="480" alt="ScreenShot_App (4)" src="https://github.com/user-attachments/assets/74077a24-1831-46d7-9193-50454384ba14" />

</div>

## 🏗️ Architecture

```
┌─────────────────┐
│  Flutter App    │ (Mobile/Web/Desktop)
│  (nazaarabox)   │
└────────┬─────────┘
         │ HTTPS
         │ API Calls
         ▼
┌─────────────────┐
│  Node.js API    │ (Public Endpoints)
│  (backend)      │
└────────┬─────────┘
         │
         │ Shared Database
         ▼
┌─────────────────┐
│  MySQL/Postgres │
│  (Laravel DB)   │
└─────────────────┘
```

## 🛠️ Tech Stack

### Frontend (Mobile App)
- **Framework**: Flutter 3.8.1+
- **Language**: Dart
- **State Management**: Provider/GetX
- **HTTP Client**: Dio
- **Video Player**: Video Player Plugin
- **Platforms**: Android, iOS, Web, Windows, macOS, Linux

### Backend
- **Runtime**: Node.js 18+
- **Framework**: Express.js 4.19.2
- **Database**: Knex.js (MySQL2/PostgreSQL)
- **Security**: Helmet, CORS
- **Logging**: Pino
- **Email**: Nodemailer

## 🚀 Getting Started

### Prerequisites

- Flutter 3.8.1+ (for mobile app)
- Node.js 18+ (for backend)
- MySQL/PostgreSQL database

### Installation

#### Mobile App

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
   - Edit `lib/services/api_service.dart`
   - Set `USE_PRODUCTION = true` for production API
   - Or configure local development URL

4. **Run the app**
   ```bash
   flutter run
   ```

#### Backend

1. **Navigate to backend directory**
   ```bash
   cd backend
   ```

2. **Install dependencies**
   ```bash
   npm install
   ```

3. **Configure environment variables**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

4. **Start the server**
   ```bash
   npm start
   ```

## 📁 Project Structure

```
NAZAARABOX/
├── Mobile App/          # Flutter mobile application
│   ├── lib/
│   │   ├── models/      # Data models
│   │   ├── pages/       # App screens
│   │   ├── services/    # API services
│   │   └── widgets/     # Reusable widgets
│   └── pubspec.yaml
├── backend/             # Node.js/Express API
│   ├── src/
│   │   ├── controllers/ # Route controllers
│   │   ├── routes/      # API routes
│   │   ├── middleware/  # Express middleware
│   │   └── utils/       # Utility functions
│   └── package.json
└── README.md
```

## 📚 Documentation

- [Complete Project Analysis](PROJECT_ANALYSIS.md) - Detailed technical documentation
- [Mobile App README](Mobile%20App/README.md) - Mobile app setup and configuration
- [Backend Documentation](backend/documentation.md) - API documentation

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📝 License

This project is licensed under the MIT License.

## 👤 Author

**coding-with-maaz**

- GitHub: [@coding-with-maaz](https://github.com/coding-with-maaz)

## 🙏 Acknowledgments

- Inspired by Netflix's user experience
- Built with Flutter and Node.js
- Thanks to all contributors and the open-source community

---

<div align="center">

**⭐ Star this repo if you find it helpful! ⭐**

Made with ❤️ by coding-with-maaz

</div>
