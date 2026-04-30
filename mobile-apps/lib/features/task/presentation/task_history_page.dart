import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../domain/model/task_model.dart';
import 'task_provider.dart';
import 'checkin_page.dart';

class TaskHistoryPage extends StatefulWidget {
  const TaskHistoryPage({super.key});

  @override
  State<TaskHistoryPage> createState() => _TaskHistoryPageState();
}

class _TaskHistoryPageState extends State<TaskHistoryPage> {
  // Filter dinamis — hanya prioritas, kategori, tanggal (status selalu closed)
  String _priorityFilter = '';
  String _categoryFilter = '';
  DateTimeRange? _dateRange;

  List<TaskModel> _closedTasks = [];
  bool _isLoading = true;
  String _errorMessage = '';

  final List<Map<String, String>> _priorityFilters = [
    {'label': 'Semua', 'value': ''},
    {'label': 'High', 'value': 'High'},
    {'label': 'Medium', 'value': 'Medium'},
    {'label': 'Low', 'value': 'Low'},
  ];

  @override
  void initState() {
    super.initState();
    _loadHistory();
  }

  Future<void> _loadHistory() async {
    setState(() {
      _isLoading = true;
      _errorMessage = '';
    });

    try {
      final provider = context.read<TaskProvider>();
      final allTasks = await provider.fetchAllTasksForHistory();
      if (!mounted) return;

      // Hanya ambil yang statusnya closed
      setState(() {
        _closedTasks = allTasks
            .where((t) => t.status.toLowerCase() == 'closed')
            .toList();
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _errorMessage = e.toString().replaceFirst('Exception: ', '');
        _isLoading = false;
      });
    }
  }

  List<TaskModel> get _filteredTasks {
    return _closedTasks.where((task) {
      // Filter prioritas
      if (_priorityFilter.isNotEmpty &&
          task.priority.toLowerCase() != _priorityFilter.toLowerCase()) {
        return false;
      }
      // Filter kategori
      if (_categoryFilter.isNotEmpty &&
          task.categoryName != _categoryFilter) {
        return false;
      }
      // Filter tanggal
      if (_dateRange != null) {
        final taskDate = task.updatedAt;
        if (taskDate.isBefore(_dateRange!.start) ||
            taskDate.isAfter(
              _dateRange!.end.add(const Duration(days: 1)),
            )) {
          return false;
        }
      }
      return true;
    }).toList();
  }

  /// Ambil daftar kategori unik dari data
  List<String> get _uniqueCategories {
    final categories =
        _closedTasks.map((t) => t.categoryName).toSet().toList();
    categories.sort();
    return categories;
  }

  String _formatDate(DateTime date) {
    return '${date.day.toString().padLeft(2, '0')}/${date.month.toString().padLeft(2, '0')}/${date.year}';
  }

  String _formatDateTime(DateTime date) {
    return '${_formatDate(date)} ${date.hour.toString().padLeft(2, '0')}:${date.minute.toString().padLeft(2, '0')}';
  }

  Future<void> _pickDateRange() async {
    final picked = await showDateRangePicker(
      context: context,
      firstDate: DateTime(2024),
      lastDate: DateTime.now(),
      initialDateRange: _dateRange,
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: Theme.of(context).colorScheme,
          ),
          child: child!,
        );
      },
    );

    if (picked != null) {
      setState(() => _dateRange = picked);
    }
  }

  void _clearAllFilters() {
    setState(() {
      _priorityFilter = '';
      _categoryFilter = '';
      _dateRange = null;
    });
  }

  bool get _hasActiveFilter =>
      _priorityFilter.isNotEmpty ||
      _categoryFilter.isNotEmpty ||
      _dateRange != null;

  @override
  Widget build(BuildContext context) {
    final colorScheme = Theme.of(context).colorScheme;
    final filtered = _filteredTasks;

    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: const Text(
          'Riwayat Tugas',
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
        backgroundColor: colorScheme.primary,
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          if (_hasActiveFilter)
            IconButton(
              icon: const Icon(Icons.filter_alt_off),
              tooltip: 'Hapus Semua Filter',
              onPressed: _clearAllFilters,
            ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _errorMessage.isNotEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.error_outline,
                          size: 64, color: Colors.red[300]),
                      const SizedBox(height: 16),
                      Text(
                        _errorMessage,
                        textAlign: TextAlign.center,
                        style: TextStyle(color: Colors.grey[600]),
                      ),
                      const SizedBox(height: 24),
                      FilledButton.icon(
                        onPressed: _loadHistory,
                        icon: const Icon(Icons.refresh),
                        label: const Text('Coba Lagi'),
                      ),
                    ],
                  ),
                )
              : Column(
                  children: [
                    // === Filter Section ===
                    Container(
                      color: Colors.white,
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Filter chips: prioritas, kategori, tanggal
                          SingleChildScrollView(
                            scrollDirection: Axis.horizontal,
                            child: Row(
                              children: [
                                // Filter Prioritas
                                _buildDropdownChip(
                                  label: _priorityFilter.isEmpty
                                      ? 'Prioritas'
                                      : _priorityFilter,
                                  isActive: _priorityFilter.isNotEmpty,
                                  icon: Icons.flag_outlined,
                                  onTap: () => _showPriorityFilter(),
                                ),
                                const SizedBox(width: 8),

                                // Filter Kategori
                                _buildDropdownChip(
                                  label: _categoryFilter.isEmpty
                                      ? 'Kategori'
                                      : _categoryFilter,
                                  isActive: _categoryFilter.isNotEmpty,
                                  icon: Icons.category_outlined,
                                  onTap: () => _showCategoryFilter(),
                                ),
                                const SizedBox(width: 8),

                                // Filter Tanggal
                                _buildDropdownChip(
                                  label: _dateRange == null
                                      ? 'Tanggal'
                                      : '${_formatDate(_dateRange!.start)} - ${_formatDate(_dateRange!.end)}',
                                  isActive: _dateRange != null,
                                  icon: Icons.calendar_month_outlined,
                                  onTap: _pickDateRange,
                                ),
                              ],
                            ),
                          ),

                          const SizedBox(height: 12),

                          // Info jumlah hasil
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 10,
                                  vertical: 4,
                                ),
                                decoration: BoxDecoration(
                                  color: colorScheme.primary.withValues(alpha: 0.1),
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Text(
                                  '${filtered.length} dari ${_closedTasks.length} tugas selesai',
                                  style: TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.bold,
                                    color: colorScheme.primary,
                                  ),
                                ),
                              ),
                              if (_hasActiveFilter) ...[
                                const SizedBox(width: 8),
                                GestureDetector(
                                  onTap: _clearAllFilters,
                                  child: Text(
                                    'Reset filter',
                                    style: TextStyle(
                                      fontSize: 12,
                                      color: Colors.red[400],
                                      fontWeight: FontWeight.w500,
                                    ),
                                  ),
                                ),
                              ],
                            ],
                          ),
                        ],
                      ),
                    ),

                    // === Task List ===
                    Expanded(
                      child: filtered.isEmpty
                          ? Center(
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Container(
                                    padding: const EdgeInsets.all(32),
                                    decoration: BoxDecoration(
                                      color: Colors.grey[100],
                                      shape: BoxShape.circle,
                                    ),
                                    child: Icon(
                                      _hasActiveFilter
                                          ? Icons.filter_list_off
                                          : Icons.history,
                                      size: 64,
                                      color: Colors.grey[400],
                                    ),
                                  ),
                                  const SizedBox(height: 24),
                                  Text(
                                    _hasActiveFilter
                                        ? 'Tidak ada hasil'
                                        : 'Belum ada riwayat tugas',
                                    style: const TextStyle(
                                      fontSize: 18,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                  const SizedBox(height: 8),
                                  Text(
                                    _hasActiveFilter
                                        ? 'Coba ubah filter untuk melihat data lain.'
                                        : 'Tugas yang sudah ditutup akan muncul di sini.',
                                    textAlign: TextAlign.center,
                                    style: TextStyle(
                                      fontSize: 14,
                                      color: Colors.grey[500],
                                    ),
                                  ),
                                  if (_hasActiveFilter) ...[
                                    const SizedBox(height: 16),
                                    TextButton(
                                      onPressed: _clearAllFilters,
                                      child: const Text('Reset Filter'),
                                    ),
                                  ],
                                ],
                              ),
                            )
                          : RefreshIndicator(
                              onRefresh: _loadHistory,
                              child: ListView.builder(
                                padding: const EdgeInsets.all(16),
                                itemCount: filtered.length,
                                itemBuilder: (context, index) {
                                  final task = filtered[index];
                                  return _buildTaskCard(task, colorScheme);
                                },
                              ),
                            ),
                    ),
                  ],
                ),
    );
  }

  Widget _buildDropdownChip({
    required String label,
    required bool isActive,
    required IconData icon,
    required VoidCallback onTap,
  }) {
    final colorScheme = Theme.of(context).colorScheme;
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        decoration: BoxDecoration(
          color: isActive ? colorScheme.primary.withValues(alpha: 0.1) : Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isActive ? colorScheme.primary : Colors.grey[300]!,
          ),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              icon,
              size: 16,
              color: isActive ? colorScheme.primary : Colors.grey[600],
            ),
            const SizedBox(width: 6),
            Text(
              label,
              style: TextStyle(
                fontSize: 13,
                color: isActive ? colorScheme.primary : Colors.grey[700],
                fontWeight: isActive ? FontWeight.bold : FontWeight.normal,
              ),
            ),
            const SizedBox(width: 4),
            Icon(
              Icons.keyboard_arrow_down,
              size: 16,
              color: isActive ? colorScheme.primary : Colors.grey[500],
            ),
          ],
        ),
      ),
    );
  }

  void _showPriorityFilter() {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        return Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: Colors.grey[300],
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 24),
              const Text(
                'Filter Prioritas',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 16),
              ..._priorityFilters.map((filter) {
                final isSelected = _priorityFilter == filter['value'];
                return ListTile(
                  leading: Icon(
                    isSelected
                        ? Icons.radio_button_checked
                        : Icons.radio_button_off,
                    color: isSelected
                        ? Theme.of(context).colorScheme.primary
                        : Colors.grey[400],
                  ),
                  title: Text(
                    filter['label']!,
                    style: TextStyle(
                      fontWeight:
                          isSelected ? FontWeight.bold : FontWeight.normal,
                    ),
                  ),
                  onTap: () {
                    setState(() => _priorityFilter = filter['value']!);
                    Navigator.pop(context);
                  },
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                );
              }),
              const SizedBox(height: 16),
            ],
          ),
        );
      },
    );
  }

  void _showCategoryFilter() {
    final categories = _uniqueCategories;

    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        return Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: Colors.grey[300],
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 24),
              const Text(
                'Filter Kategori',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 16),
              // Opsi "Semua"
              ListTile(
                leading: Icon(
                  _categoryFilter.isEmpty
                      ? Icons.radio_button_checked
                      : Icons.radio_button_off,
                  color: _categoryFilter.isEmpty
                      ? Theme.of(context).colorScheme.primary
                      : Colors.grey[400],
                ),
                title: Text(
                  'Semua',
                  style: TextStyle(
                    fontWeight: _categoryFilter.isEmpty
                        ? FontWeight.bold
                        : FontWeight.normal,
                  ),
                ),
                onTap: () {
                  setState(() => _categoryFilter = '');
                  Navigator.pop(context);
                },
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              ...categories.map((cat) {
                final isSelected = _categoryFilter == cat;
                return ListTile(
                  leading: Icon(
                    isSelected
                        ? Icons.radio_button_checked
                        : Icons.radio_button_off,
                    color: isSelected
                        ? Theme.of(context).colorScheme.primary
                        : Colors.grey[400],
                  ),
                  title: Text(
                    cat,
                    style: TextStyle(
                      fontWeight:
                          isSelected ? FontWeight.bold : FontWeight.normal,
                    ),
                  ),
                  onTap: () {
                    setState(() => _categoryFilter = cat);
                    Navigator.pop(context);
                  },
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                );
              }),
              const SizedBox(height: 16),
            ],
          ),
        );
      },
    );
  }

  Widget _buildTaskCard(TaskModel task, ColorScheme colorScheme) {
    return Card(
      elevation: 2,
      margin: const EdgeInsets.only(bottom: 12),
      shadowColor: Colors.black.withValues(alpha: 0.1),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
      ),
      child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => CheckinPage(task: task),
            ),
          );
        },
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header: Tiket & badge selesai
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    task.ticketNumber,
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.grey[500],
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                  Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.green.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.check_circle,
                            size: 14, color: Colors.green[700]),
                        const SizedBox(width: 4),
                        Text(
                          'Selesai',
                          style: TextStyle(
                            color: Colors.green[700],
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),

              // Subject
              Text(
                task.subject,
                style: const TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 4),

              // Kategori
              Text(
                task.categoryName,
                style: TextStyle(
                  fontSize: 13,
                  color: Colors.grey[500],
                ),
              ),
              const SizedBox(height: 12),

              // Lokasi & tanggal
              Row(
                children: [
                  Icon(Icons.location_on_outlined,
                      size: 16, color: Colors.grey[400]),
                  const SizedBox(width: 4),
                  Expanded(
                    child: Text(
                      '${task.departmentName} — ${task.locationDetail}',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey[500],
                      ),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 6),

              // Waktu selesai + priority
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      Icon(Icons.access_time,
                          size: 14, color: Colors.grey[400]),
                      const SizedBox(width: 4),
                      Text(
                        _formatDateTime(task.updatedAt),
                        style: TextStyle(
                          fontSize: 11,
                          color: Colors.grey[400],
                        ),
                      ),
                    ],
                  ),
                  _buildPriorityBadge(task.priority),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildPriorityBadge(String priority) {
    Color color;
    IconData icon;

    switch (priority.toLowerCase()) {
      case 'high':
        color = Colors.red[700]!;
        icon = Icons.keyboard_double_arrow_up;
        break;
      case 'medium':
        color = Colors.orange[700]!;
        icon = Icons.keyboard_arrow_up;
        break;
      default:
        color = Colors.green[700]!;
        icon = Icons.keyboard_arrow_down;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: color),
          const SizedBox(width: 2),
          Text(
            priority,
            style: TextStyle(
              fontSize: 11,
              color: color,
              fontWeight: FontWeight.bold,
            ),
          ),
        ],
      ),
    );
  }
}
