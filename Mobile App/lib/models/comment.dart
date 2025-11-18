class Comment {
  final int id;
  final String? parentId;
  final String name;
  final String email;
  final String comment;
  final String status;
  final bool isAdminReply;
  final String? adminId;
  final String createdAt;
  final String updatedAt;
  final List<Comment>? replies;

  Comment({
    required this.id,
    this.parentId,
    required this.name,
    required this.email,
    required this.comment,
    required this.status,
    required this.isAdminReply,
    this.adminId,
    required this.createdAt,
    required this.updatedAt,
    this.replies,
  });

  factory Comment.fromJson(Map<String, dynamic> json) {
    // Handle is_admin_reply as both boolean and integer (0/1)
    bool isAdmin = false;
    if (json['is_admin_reply'] != null) {
      if (json['is_admin_reply'] is bool) {
        isAdmin = json['is_admin_reply'] as bool;
      } else if (json['is_admin_reply'] is int) {
        isAdmin = (json['is_admin_reply'] as int) == 1;
      } else if (json['is_admin_reply'] is String) {
        isAdmin = json['is_admin_reply'] == '1' || json['is_admin_reply'].toString().toLowerCase() == 'true';
      }
    }
    
    return Comment(
      id: json['id'] is int ? json['id'] as int : int.tryParse(json['id'].toString()) ?? 0,
      parentId: json['parent_id']?.toString(),
      name: json['name'] as String? ?? '',
      email: json['email'] as String? ?? '',
      comment: json['comment'] as String? ?? '',
      status: json['status'] as String? ?? 'pending',
      isAdminReply: isAdmin,
      adminId: json['admin_id']?.toString(),
      createdAt: json['created_at']?.toString() ?? '',
      updatedAt: json['updated_at']?.toString() ?? '',
      replies: json['replies'] != null && json['replies'] is List
          ? (json['replies'] as List<dynamic>)
              .map((reply) {
                try {
                  return Comment.fromJson(reply as Map<String, dynamic>);
                } catch (e) {
                  return null;
                }
              })
              .whereType<Comment>()
              .toList()
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'parent_id': parentId,
      'name': name,
      'email': email,
      'comment': comment,
      'status': status,
      'is_admin_reply': isAdminReply,
      'admin_id': adminId,
      'created_at': createdAt,
      'updated_at': updatedAt,
      'replies': replies?.map((reply) => reply.toJson()).toList(),
    };
  }
}

