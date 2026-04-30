import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../domain/model/task_model.dart';
import '../../../core/storage/secure_storage.dart';
import 'task_provider.dart';
import 'task_list_page.dart';

class TaskReportPage extends StatefulWidget {
  final TaskModel task;

  const TaskReportPage({super.key, required this.task});

  @override
  State<TaskReportPage> createState() => _TaskReportPageState();
}

class _TaskReportPageState extends State<TaskReportPage> {
  final _noteController = TextEditingController();
  final _formKey = GlobalKey<FormState>();

  late final Stream<int> _timerStream;

  /// Waktu mulai pengerjaan — diambil dari SecureStorage agar persist
  DateTime? _startTime;
  bool _isTimerReady = false;

  @override
  void initState() {
    super.initState();
    _timerStream = Stream.periodic(const Duration(seconds: 1), (tick) => tick);
    _initTimer();
  }

  /// Ambil atau buat start time di SecureStorage
  Future<void> _initTimer() async {
    final ticketId = widget.task.id;
    DateTime? saved = await SecureStorage.getTimerStart(ticketId);

    if (saved == null) {
      // Pertama kali masuk → simpan waktu sekarang
      saved = DateTime.now();
      await SecureStorage.saveTimerStart(ticketId, saved);
    }

    if (!mounted) return;

    setState(() {
      _startTime = saved;
      _isTimerReady = true;
    });
  }

  Duration get _elapsed {
    if (_startTime == null) return Duration.zero;
    return DateTime.now().difference(_startTime!);
  }

  @override
  void dispose() {
    _noteController.dispose();
    super.dispose();
  }

  String _formatDuration(Duration duration) {
    final hours = duration.inHours.toString().padLeft(2, '0');
    final minutes = duration.inMinutes.remainder(60).toString().padLeft(2, '0');
    final seconds = duration.inSeconds.remainder(60).toString().padLeft(2, '0');
    return '$hours:$minutes:$seconds';
  }

  Future<void> _submitReport() async {
    if (!_formKey.currentState!.validate()) return;

    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Konfirmasi Laporan'),
        content: const Text(
          'Apakah Anda yakin pekerjaan sudah selesai dan ingin mengirim laporan?',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Batal'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Kirim'),
          ),
        ],
      ),
    );

    if (!mounted) return;

    if (confirm != true) return;

    final provider = context.read<TaskProvider>();

    await provider.resolveTicket(
      ticketId: widget.task.id,
      note: _noteController.text.trim(),
    );

    if (!mounted) return;

    if (provider.isResolved) {
      // Bersihkan timer dari storage setelah berhasil
      await SecureStorage.clearTimerStart(widget.task.id);

      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Laporan berhasil dikirim! Pekerjaan Selesai.'),
          backgroundColor: Colors.green,
          behavior: SnackBarBehavior.floating,
        ),
      );
      Navigator.pushAndRemoveUntil(
        context,
        MaterialPageRoute(builder: (_) => const TaskListPage()),
        (route) => false,
      );
    } else if (provider.resolveError.isNotEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(provider.resolveError),
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
          'Buat Laporan',
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
                child: Form(
                  key: _formKey,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // === Info Tugas ===
                      Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: colorScheme.primary.withValues(alpha: 0.05),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(
                            color: colorScheme.primary.withValues(alpha: 0.2),
                          ),
                        ),
                        child: Row(
                          children: [
                            Icon(
                              Icons.assignment_turned_in,
                              color: colorScheme.primary,
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    widget.task.subject,
                                    style: const TextStyle(
                                      fontWeight: FontWeight.bold,
                                      fontSize: 16,
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    widget.task.ticketNumber,
                                    style: TextStyle(
                                      color: Colors.grey[500],
                                      fontSize: 12,
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    '${widget.task.departmentName} — ${widget.task.locationDetail}',
                                    style: TextStyle(
                                      color: Colors.grey[700],
                                      fontSize: 13,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 24),

                      // === Timer ===
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
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(
                                  Icons.timer_outlined,
                                  color: colorScheme.primary,
                                  size: 20,
                                ),
                                const SizedBox(width: 8),
                                Text(
                                  'Waktu Pengerjaan',
                                  style: TextStyle(
                                    fontSize: 14,
                                    color: Colors.grey[600],
                                    fontWeight: FontWeight.w500,
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 12),
                            _isTimerReady
                                ? StreamBuilder<int>(
                                    stream: _timerStream,
                                    builder: (context, snapshot) {
                                      return Text(
                                        _formatDuration(_elapsed),
                                        style: TextStyle(
                                          fontSize: 48,
                                          fontWeight: FontWeight.bold,
                                          color: colorScheme.primary,
                                        ),
                                      );
                                    },
                                  )
                                : SizedBox(
                                    height: 48,
                                    child: Center(
                                      child: CircularProgressIndicator(
                                        color: colorScheme.primary,
                                        strokeWidth: 2,
                                      ),
                                    ),
                                  ),
                            const SizedBox(height: 8),
                            Text(
                              'Timer berjalan sejak check-in pertama',
                              style: TextStyle(
                                fontSize: 12,
                                color: Colors.grey[400],
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 24),

                      // === Catatan Teknisi ===
                      const Text(
                        'Catatan Teknisi',
                        style: TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 16,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Wajib diisi — deskripsikan pekerjaan yang telah dilakukan',
                        style: TextStyle(fontSize: 12, color: Colors.grey[500]),
                      ),
                      const SizedBox(height: 12),
                      TextFormField(
                        controller: _noteController,
                        maxLines: 5,
                        validator: (value) {
                          if (value == null || value.trim().isEmpty) {
                            return 'Catatan tidak boleh kosong';
                          }
                          if (value.trim().length < 10) {
                            return 'Catatan minimal 10 karakter';
                          }
                          return null;
                        },
                        decoration: InputDecoration(
                          hintText:
                              'Contoh: Kabel LAN sudah diganti dan dites ping lancar...',
                          hintStyle: TextStyle(color: Colors.grey[400]),
                          filled: true,
                          fillColor: Colors.white,
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(16),
                            borderSide: BorderSide(color: Colors.grey[300]!),
                          ),
                          enabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(16),
                            borderSide: BorderSide(color: Colors.grey[300]!),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(16),
                            borderSide: BorderSide(
                              color: colorScheme.primary,
                              width: 2,
                            ),
                          ),
                          errorBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(16),
                            borderSide: BorderSide(color: Colors.red[300]!),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),

            // === Tombol Submit ===
            Consumer<TaskProvider>(
              builder: (context, provider, _) {
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
                  child: SizedBox(
                    width: double.infinity,
                    height: 56,
                    child: FilledButton.icon(
                      style: FilledButton.styleFrom(
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(16),
                        ),
                      ),
                      icon: provider.isResolving
                          ? const SizedBox.shrink()
                          : const Icon(Icons.send),
                      label: provider.isResolving
                          ? const SizedBox(
                              height: 20,
                              width: 20,
                              child: CircularProgressIndicator(
                                color: Colors.white,
                                strokeWidth: 2,
                              ),
                            )
                          : const Text(
                              'Kirim Laporan & Selesai',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                      onPressed: provider.isResolving ? null : _submitReport,
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
}
