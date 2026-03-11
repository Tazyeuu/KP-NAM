import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'features/auth/presentation/login_page.dart';
import 'features/task/presentation/task_provider.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [ChangeNotifierProvider(create: (_) => TaskProvider())],
      child: MaterialApp(
        debugShowCheckedModeBanner: false,
        title: 'Helpdesk Teknisi',
        theme: ThemeData(
          colorScheme: ColorScheme.fromSeed(seedColor: Colors.green),
          useMaterial3: true,
        ),
        home: const LoginPage(),
      ),
    );
  }
}
