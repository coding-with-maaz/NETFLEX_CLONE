// This is a basic Flutter widget test for Nazaara Box app.
//
// To perform an interaction with a widget in your test, use the WidgetTester
// utility in the flutter_test package. For example, you can send tap and scroll
// gestures. You can also use WidgetTester to find child widgets in the widget
// tree, read text, and verify that the values of widget properties are correct.

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:nazaarabox/main.dart';

void main() {
  testWidgets('Nazaara Box app smoke test', (WidgetTester tester) async {
    // Build our app and trigger a frame.
    await tester.pumpWidget(const NazaaraBoxApp());

    // Wait for the widget tree to settle
    await tester.pumpAndSettle();

    // Verify that the app builds without errors
    expect(find.byType(MaterialApp), findsOneWidget);
    
    // The HomePage should be present
    // Note: Since the HomePage makes API calls, we're just testing that it builds
    expect(find.byType(Scaffold), findsOneWidget);
  });
}
