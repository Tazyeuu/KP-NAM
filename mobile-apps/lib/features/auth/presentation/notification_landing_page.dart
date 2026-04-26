import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/storage/secure_storage.dart';
import '../../task/presentation/task_provider.dart';
import '../../task/presentation/task_list_page.dart';
import '../../task/presentation/checkin_page.dart';
import '../../auth/presentation/login_page.dart';

class NotificationLandingPage extends StatefulWidget {
  final int ticketId;

  const NotificationLandingPage({super.key, required this.ticketId});

  @override
  State<NotificationLandingPage> createState() =>
      _NotificationLandingPageState();
}

class _NotificationLandingPageState extends State<NotificationLandingPage> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadAndNavigate();
    });
  }

  Future<void> _loadAndNavigate() async {
    // Cek token dulu
    final token = await SecureStorage.getToken();
    final userIdStr = await SecureStorage.getUserId();

    if (!mounted) return;

    // Token tidak ada → ke login
    if (token == null || userIdStr == null) {
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => const LoginPage()),
      );
      return;
    }

    final provider = context.read<TaskProvider>();
    await provider.fetchMyTasks(forceRefresh: true);

    if (!mounted) return;

    // Fetch gagal → ke task list
    if (provider.taskStatus == TaskStatus.error) {
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => const TaskListPage()),
      );
      return;
    }

    // Cari task yang sesuai
    final task = provider.tasks
        .where((t) => t.id == widget.ticketId)
        .firstOrNull;

    if (task != null) {
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => CheckinPage(task: task)),
      );
    } else {
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => const TaskListPage()),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            CircularProgressIndicator(
              color: Theme.of(context).colorScheme.primary,
            ),
            const SizedBox(height: 16),
            Text(
              'Membuka tugas...',
              style: TextStyle(color: Colors.grey[600], fontSize: 14),
            ),
          ],
        ),
      ),
    );
  }
}
