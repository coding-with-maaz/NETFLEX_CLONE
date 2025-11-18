// Conditional export based on platform
export 'iframe_player_stub.dart'
    if (dart.library.html) 'iframe_player_web.dart';
