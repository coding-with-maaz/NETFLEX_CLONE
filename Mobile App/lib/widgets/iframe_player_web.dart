import 'package:flutter/material.dart';
import 'dart:ui' as ui;
// ignore: avoid_web_libraries_in_flutter
import 'dart:html' as html;

class IframePlayer extends StatefulWidget {
  final String url;
  final String? iframeStyle;
  final Map<String, String> iframeAttributes;

  const IframePlayer({
    Key? key,
    required this.url,
    this.iframeStyle,
    this.iframeAttributes = const {},
  }) : super(key: key);

  @override
  State<IframePlayer> createState() => _IframePlayerState();
}

class _IframePlayerState extends State<IframePlayer> {
  final String viewId = 'iframe-${DateTime.now().millisecondsSinceEpoch}';

  @override
  void initState() {
    super.initState();
    _registerIframe();
  }

  void _registerIframe() {
    // ignore: undefined_prefixed_name
    ui.platformViewRegistry.registerViewFactory(
      viewId,
      (int viewId) {
        final iframe = html.IFrameElement()
          ..src = widget.url;

        // Apply custom iframe style if provided
        if (widget.iframeStyle != null) {
          // Parse CSS string and apply to iframe
          final styleStr = widget.iframeStyle!;
          final styles = styleStr.split(';');
          for (var style in styles) {
            final parts = style.split(':');
            if (parts.length == 2) {
              final property = parts[0].trim();
              final value = parts[1].trim();
              
              switch (property.toLowerCase()) {
                case 'width':
                  iframe.style.width = value;
                  break;
                case 'height':
                  iframe.style.height = value;
                  break;
                case 'position':
                  iframe.style.position = value;
                  break;
                case 'top':
                  iframe.style.top = value;
                  break;
                case 'left':
                  iframe.style.left = value;
                  break;
                case 'border':
                  iframe.style.border = value;
                  break;
              }
            }
          }
        } else {
          // Default styles
          iframe.style.border = 'none';
          iframe.style.width = '100%';
          iframe.style.height = '100%';
        }

        // Apply iframe attributes
        if (widget.iframeAttributes.containsKey('scrolling')) {
          iframe.setAttribute('scrolling', widget.iframeAttributes['scrolling']!);
        }
        
        // Apply allowfullscreen
        if (widget.iframeAttributes.containsKey('allowfullscreen') ||
            !widget.iframeAttributes.containsKey('allowfullscreen')) {
          iframe.allowFullscreen = true;
        }

        // Apply allow attribute
        if (widget.iframeAttributes.containsKey('allow')) {
          iframe.allow = widget.iframeAttributes['allow']!;
        } else {
          iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen';
        }

        // Default attributes
        iframe.setAttribute('referrerpolicy', 'no-referrer-when-downgrade');
        iframe.setAttribute('loading', 'lazy');

        return iframe;
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return HtmlElementView(viewType: viewId);
  }
}

