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

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'assigned':
        return Colors.orange;
      case 'in progress':
        return Colors.blue;
      case 'resolved':
        return Colors.green;
      default:
        return Colors.grey;
    }
  }

  IconData _getStatusIcon(String status) {
    switch (status.toLowerCase()) {
      case 'assigned':
        return Icons.assignment_ind_outlined;
      case 'in progress':
        return Icons.build_outlined;
      case 'resolved':
        return Icons.check_circle_outline;
      default:
        return Icons.info_outline;
    }
  }

  String _formatDate(DateTime date) {
    return '${date.day}/${date.month}/${date.year} ${date.hour.toString().padLeft(2, '0')}:${date.minute.toString().padLeft(2, '0')}';
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
                          color: colorScheme.primary.withValues(alpha: 0.1),
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

                    // === Info Pekerjaan ===
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(24),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(16),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.05),
                            blurRadius: 10,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
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
                          Text(
                            widget.task.ticketNumber,
                            style: TextStyle(
                              fontSize: 13,
                              color: Colors.grey[500],
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            widget.task.subject,
                            style: const TextStyle(
                              fontSize: 22,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 8),
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
                          _buildInfoRow(
                            icon: Icons.category_outlined,
                            iconColor: Colors.purple[800]!,
                            bgColor: Colors.purple[50]!,
                            text: widget.task.categoryName,
                          ),
                          const SizedBox(height: 16),
                          _buildInfoRow(
                            icon: Icons.meeting_room,
                            iconColor: Colors.orange[800]!,
                            bgColor: Colors.orange[50]!,
                            text: widget.task.departmentName,
                          ),
                          const SizedBox(height: 16),
                          _buildInfoRow(
                            icon: Icons.location_on_outlined,
                            iconColor: Colors.green[800]!,
                            bgColor: Colors.green[50]!,
                            text: widget.task.locationDetail,
                          ),
                          const SizedBox(height: 16),
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
                    const SizedBox(height: 24),

                    // === Riwayat Log ===
                    if (widget.task.logs.isNotEmpty) ...[
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(24),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(16),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.05),
                              blurRadius: 10,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'RIWAYAT TIKET',
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.bold,
                                color: Colors.grey[500],
                                letterSpacing: 1.5,
                              ),
                            ),
                            const SizedBox(height: 16),

                            // Timeline
                            ...widget.task.logs.asMap().entries.map((entry) {
                              final index = entry.key;
                              final log = entry.value;
                              final isLast =
                                  index == widget.task.logs.length - 1;
                              final color = _getStatusColor(log.statusTo);

                              return IntrinsicHeight(
                                child: Row(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    // Garis timeline
                                    Column(
                                      children: [
                                        Container(
                                          width: 32,
                                          height: 32,
                                          decoration: BoxDecoration(
                                            color: color.withValues(alpha: 0.15),
                                            shape: BoxShape.circle,
                                          ),
                                          child: Icon(
                                            _getStatusIcon(log.statusTo),
                                            size: 16,
                                            color: color,
                                          ),
                                        ),
                                        if (!isLast)
                                          Expanded(
                                            child: Container(
                                              width: 2,
                                              color: Colors.grey[200],
                                            ),
                                          ),
                                      ],
                                    ),
                                    const SizedBox(width: 12),

                                    // Konten log
                                    Expanded(
                                      child: Padding(
                                        padding: const EdgeInsets.only(
                                          bottom: 20,
                                        ),
                                        child: Column(
                                          crossAxisAlignment:
                                              CrossAxisAlignment.start,
                                          children: [
                                            // Status badge
                                            Container(
                                              padding:
                                                  const EdgeInsets.symmetric(
                                                    horizontal: 8,
                                                    vertical: 3,
                                                  ),
                                              decoration: BoxDecoration(
                                                color: color.withValues(alpha: 0.15),
                                                borderRadius:
                                                    BorderRadius.circular(6),
                                              ),
                                              child: Text(
                                                log.statusTo,
                                                style: TextStyle(
                                                  fontSize: 11,
                                                  fontWeight: FontWeight.bold,
                                                  color: color,
                                                ),
                                              ),
                                            ),
                                            const SizedBox(height: 4),

                                            // Catatan
                                            Text(
                                              log.note,
                                              style: TextStyle(
                                                fontSize: 13,
                                                color: Colors.grey[700],
                                                height: 1.4,
                                              ),
                                            ),
                                            const SizedBox(height: 4),

                                            // Waktu
                                            Text(
                                              _formatDate(log.createdAt),
                                              style: TextStyle(
                                                fontSize: 11,
                                                color: Colors.grey[400],
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              );
                            }),
                          ],
                        ),
                      ),
                      const SizedBox(height: 24),
                    ],
                  ],
                ),
              ),
            ),

            // Tombol Check-in
            Consumer<TaskProvider>(
              builder: (context, provider, _) {
                final status = widget.task.status.toLowerCase();

                return Container(
                  padding: const EdgeInsets.all(24),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.05),
                        blurRadius: 10,
                        offset: const Offset(0, -4),
                      ),
                    ],
                  ),
                  child: status == 'assigned'
                      // Tombol Check-in
                      ? SizedBox(
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
                            onPressed: provider.isLoading
                                ? null
                                : _handleCheckIn,
                          ),
                        )
                      : status == 'in progress'
                      // Tombol Buat Laporan
                      ? SizedBox(
                          width: double.infinity,
                          height: 56,
                          child: FilledButton.icon(
                            style: FilledButton.styleFrom(
                              backgroundColor: Colors.blue[700],
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(16),
                              ),
                            ),
                            icon: const Icon(
                              Icons.assignment_outlined,
                              size: 24,
                            ),
                            label: const Text(
                              'Buat Laporan',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            onPressed: () {
                              Navigator.pushReplacement(
                                context,
                                MaterialPageRoute(
                                  builder: (_) =>
                                      TaskReportPage(task: widget.task),
                                ),
                              );
                            },
                          ),
                        )
                      // Banner Resolved
                      : Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: Colors.green[50],
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: Colors.green[200]!),
                          ),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(
                                Icons.check_circle,
                                color: Colors.green[700],
                              ),
                              const SizedBox(width: 8),
                              Text(
                                'Tugas telah diselesaikan',
                                style: TextStyle(
                                  fontWeight: FontWeight.bold,
                                  color: Colors.green[700],
                                ),
                              ),
                            ],
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
