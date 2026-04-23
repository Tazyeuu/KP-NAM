import 'package:flutter/material.dart';
import '../services/connectivity_service.dart';
import 'no_internet_widget.dart';

class ConnectivityWrapper extends StatefulWidget {
  final Widget child;
  final VoidCallback? onRetry;

  const ConnectivityWrapper({super.key, required this.child, this.onRetry});

  @override
  State<ConnectivityWrapper> createState() => _ConnectivityWrapperState();
}

class _ConnectivityWrapperState extends State<ConnectivityWrapper> {
  bool _isConnected = true;

  @override
  void initState() {
    super.initState();
    _checkInitialConnectivity();
    _listenToConnectivity();
  }

  Future<void> _checkInitialConnectivity() async {
    final connected = await ConnectivityService.isConnected();
    if (mounted) setState(() => _isConnected = connected);
  }

  void _listenToConnectivity() {
    ConnectivityService.onConnectivityChanged.listen((connected) {
      if (mounted) setState(() => _isConnected = connected);
    });
  }

  @override
  Widget build(BuildContext context) {
    if (!_isConnected) {
      return Scaffold(
        backgroundColor: Colors.grey[50],
        body: NoInternetWidget(
          onRetry: () async {
            final connected = await ConnectivityService.isConnected();
            if (mounted) setState(() => _isConnected = connected);
            if (connected) widget.onRetry?.call();
          },
        ),
      );
    }

    return widget.child;
  }
}
