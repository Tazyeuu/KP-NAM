import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../domain/model/task_model.dart';
import 'task_provider.dart';
import 'task_report_page.dart';

class CheckinPage extends StatefulWidget {
  final TaskModel task;

  const CheckinPage({super.key, required this.task});

  @override
  State<CheckinPage> createState() => _CheckinPageState();
}

class _CheckinPageState extends State<CheckinPage> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<TaskProvider>().clearStatus();
    });
  }

  void _handleCheckIn() async {
    final provider = context.read<TaskProvider>();
    await provider.processCheckIn(widget.task);

    if (!mounted) return;

    if (provider.isSuccess) {
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => TaskReportPage(task: widget.task)),
      );
    } else if (provider.checkInError.isNotEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(provider.checkInError),
          backgroundColor: Colors.red[700],
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final colorScheme = Theme.of(context).colorScheme;

    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: const Text(
          'Detail Tugas',
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
        backgroundColor: colorScheme.primary,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: SafeArea(
        child: Column(
          children: [
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(24.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Center(
                      child: Container(
                        padding: const EdgeInsets.all(32),
                        decoration: BoxDecoration(
                          color: colorScheme.primary.withOpacity(0.1),
                          shape: BoxShape.circle,
                        ),
                        child: Icon(
                          Icons.my_location_rounded,
                          size: 72,
                          color: colorScheme.primary,
                        ),
                      ),
                    ),
                    const SizedBox(height: 32),

                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(24),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(16),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.05),
                            blurRadius: 10,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Header
                          Text(
                            'INFORMASI PEKERJAAN',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              color: Colors.grey[500],
                              letterSpacing: 1.5,
                            ),
                          ),
                          const SizedBox(height: 16),

                          // Ticket Number
                          Text(
                            widget.task.ticketNumber,
                            style: TextStyle(
                              fontSize: 13,
                              color: Colors.grey[500],
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                          const SizedBox(height: 4),

                          // Subject
                          Text(
                            widget.task.subject,
                            style: const TextStyle(
                              fontSize: 22,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 8),

                          // Description
                          Text(
                            widget.task.description,
                            style: TextStyle(
                              fontSize: 14,
                              color: Colors.grey[600],
                              height: 1.5,
                            ),
                          ),
                          const SizedBox(height: 20),
                          const Divider(),
                          const SizedBox(height: 20),

                          // Category
                          _buildInfoRow(
                            icon: Icons.category_outlined,
                            iconColor: Colors.purple[800]!,
                            bgColor: Colors.purple[50]!,
                            text: widget.task.categoryName,
                          ),
                          const SizedBox(height: 16),

                          // Department
                          _buildInfoRow(
                            icon: Icons.meeting_room,
                            iconColor: Colors.orange[800]!,
                            bgColor: Colors.orange[50]!,
                            text: widget.task.departmentName,
                          ),
                          const SizedBox(height: 16),

                          // Location Detail
                          _buildInfoRow(
                            icon: Icons.location_on_outlined,
                            iconColor: Colors.green[800]!,
                            bgColor: Colors.green[50]!,
                            text: widget.task.locationDetail,
                          ),
                          const SizedBox(height: 16),

                          // Koordinat
                          _buildInfoRow(
                            icon: Icons.gps_fixed,
                            iconColor: Colors.blue[800]!,
                            bgColor: Colors.blue[50]!,
                            text:
                                'Koordinat: ${widget.task.targetLat}, ${widget.task.targetLng}',
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),

            // Tombol Check-in
            Consumer<TaskProvider>(
              builder: (context, provider, _) {
                return Container(
                  padding: const EdgeInsets.all(24),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.05),
                        blurRadius: 10,
                        offset: const Offset(0, -4),
                      ),
                    ],
                  ),
                  child: SizedBox(
                    width: double.infinity,
                    height: 56,
                    child: FilledButton.icon(
                      style: FilledButton.styleFrom(
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(16),
                        ),
                      ),
                      icon: provider.isLoading
                          ? const SizedBox.shrink()
                          : const Icon(Icons.fingerprint, size: 24),
                      label: provider.isLoading
                          ? const SizedBox(
                              height: 20,
                              width: 20,
                              child: CircularProgressIndicator(
                                color: Colors.white,
                                strokeWidth: 2,
                              ),
                            )
                          : const Text(
                              'Verifikasi Jarak & Check-in',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                      onPressed: provider.isLoading ? null : _handleCheckIn,
                    ),
                  ),
                );
              },
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow({
    required IconData icon,
    required Color iconColor,
    required Color bgColor,
    required String text,
  }) {
    return Row(
      children: [
        Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: bgColor,
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(icon, color: iconColor, size: 20),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Text(
            text,
            style: TextStyle(
              fontSize: 15,
              color: Colors.grey[800],
              fontWeight: FontWeight.w500,
            ),
          ),
        ),
      ],
    );
  }
}
