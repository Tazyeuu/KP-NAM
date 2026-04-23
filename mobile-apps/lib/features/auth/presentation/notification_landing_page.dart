import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/storage/secure_storage.dart';
import '../../task/presentation/task_provider.dart';
import '../../task/presentation/task_list_page.dart';
import '../../task/presentation/checkin_page.dart';

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
    final provider = context.read<TaskProvider>();

    // Fetch semua tasks
    final userIdStr = await SecureStorage.getUserId();
    if (userIdStr == null) {
      _goToTaskList();
      return;
    }

    await provider.fetchMyTasks();

    if (!mounted) return;

    // Cari task yang sesuai dengan ticketId dari notifikasi
    final task = provider.tasks
        .where((t) => t.id == widget.ticketId)
        .firstOrNull;

    if (task != null) {
      // Task ditemukan — langsung ke CheckinPage
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => CheckinPage(task: task)),
      );
    } else {
      // Task tidak ditemukan — ke TaskListPage saja
      _goToTaskList();
    }
  }

  void _goToTaskList() {
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(builder: (_) => const TaskListPage()),
    );
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
