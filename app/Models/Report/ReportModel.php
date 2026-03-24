<?php

class ReportModel {
    private $db;

    public function __construct($conn) {
        $this->db = $conn;
    }

    // User Stats
    public function getTotalUsers() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM users");
        return $stmt->fetchColumn();
    }
    
    public function getNewUsersThisMonth() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE MONTH(CreatedAt) = MONTH(CURRENT_DATE()) AND YEAR(CreatedAt) = YEAR(CURRENT_DATE())");
        return $stmt->fetchColumn();
    }

    public function getMonthlyUserGrowth($months = 12) {
        $growthData = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = new DateTime("first day of -$i month");
            $monthName = $date->format('M Y');
            $monthYear = $date->format('Y-m');
            $growthData[$monthYear] = [
                'label' => $monthName,
                'count' => 0
            ];
        }

        $stmt = $this->db->prepare("
            SELECT 
                DATE_FORMAT(CreatedAt, '%Y-%m') as month,
                COUNT(UserID) as new_users
            FROM 
                users
            WHERE 
                CreatedAt >= :startDate
            GROUP BY 
                month
            ORDER BY 
                month ASC
        ");

        $startDate = new DateTime("first day of -" . ($months - 1) . " month");
        $stmt->execute([':startDate' => $startDate->format('Y-m-d')]);
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $row) {
            if (isset($growthData[$row['month']])) {
                $growthData[$row['month']]['count'] = (int)$row['new_users'];
            }
        }
        
        return array_values($growthData);
    }

    // Social Activity Stats
    public function getTotalPosts() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM posts");
        return $stmt->fetchColumn();
    }

    public function getPostsGrowth() {
        $stmt = $this->db->query("
            SELECT 
                (SELECT COUNT(*) FROM posts WHERE CreatedAt >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as current_month,
                (SELECT COUNT(*) FROM posts WHERE CreatedAt BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND DATE_SUB(NOW(), INTERVAL 30 DAY)) as previous_month
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['previous_month'] > 0) {
            $growth = (($result['current_month'] - $result['previous_month']) / $result['previous_month']) * 100;
            return round($growth, 1);
        }
        return 0;
    }

    public function getTotalStoryViews() {
        $stmt = $this->db->query("
            SELECT COUNT(*) FROM story_views 
            WHERE ViewedAt >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        return $stmt->fetchColumn();
    }

    public function getAvgEngagementRate() {
        $stmt = $this->db->query("
            SELECT 
                ROUND(
                    (SELECT COUNT(*) FROM interactions WHERE CreatedAt >= DATE_SUB(NOW(), INTERVAL 30 DAY)) * 100.0 / 
                    NULLIF((SELECT COUNT(*) FROM posts WHERE CreatedAt >= DATE_SUB(NOW(), INTERVAL 30 DAY)), 0)
                , 1) as engagement_rate
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['engagement_rate'] ?? 0;
    }

    public function getEngagementTrends() {
        $stmt = $this->db->prepare("
            SELECT 
                DATE(CreatedAt) as date,
                COUNT(CASE WHEN Type = 'like' OR Type = 'love' OR Type = 'haha' THEN 1 END) as likes,
                COUNT(CASE WHEN Type = 'comment' THEN 1 END) as comments
            FROM interactions
            WHERE CreatedAt >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(CreatedAt)
            ORDER BY date ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveStories() {
        $stmt = $this->db->query("
            SELECT COUNT(*) FROM stories 
            WHERE ExpiresAt > NOW() 
            AND CreatedAt >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        return $stmt->fetchColumn();
    }

    // Story Performance by User
    public function getStoryPerformance() {
        $stmt = $this->db->query("
            SELECT 
                u.UserID,
                u.Username,
                u.ProfilePic,
                COUNT(DISTINCT p.PostID) as posts,
                COUNT(DISTINCT s.StoryID) as stories,
                ROUND(
                    COUNT(DISTINCT i.InteractionID) * 100.0 / 
                    NULLIF(COUNT(DISTINCT p.PostID) + COUNT(DISTINCT s.StoryID), 0)
                , 1) as engagement_rate,
                COUNT(DISTINCT i.InteractionID) as total_interactions
            FROM users u
            LEFT JOIN posts p ON u.UserID = p.UserID
            LEFT JOIN stories s ON u.UserID = s.UserID AND s.ExpiresAt > NOW()
            LEFT JOIN interactions i ON (p.PostID = i.PostID OR s.StoryID = i.StoryID) AND i.CreatedAt >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY u.UserID
            HAVING posts > 0 OR stories > 0
            ORDER BY engagement_rate DESC
            LIMIT 10
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Real-time Activity Feed
    public function getRecentActivities($limit = 10) {
        $activities = [];
        
        // Get recent likes
        $stmt = $this->db->prepare("
            SELECT 
                u.Username,
                u.ProfilePic,
                'liked' as action,
                p.Content as target_content,
                'post' as target_type,
                i.CreatedAt as activity_time
            FROM interactions i
            JOIN users u ON i.UserID = u.UserID
            JOIN posts p ON i.PostID = p.PostID
            WHERE i.Type IN ('like', 'love', 'haha')
            ORDER BY i.CreatedAt DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $likes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($likes as $like) {
            $activities[] = $like;
        }
        
        // Get recent comments
        $stmt = $this->db->prepare("
            SELECT 
                u.Username,
                u.ProfilePic,
                'commented on' as action,
                i.CommentText as comment_text,
                p.Content as target_content,
                'post' as target_type,
                i.CreatedAt as activity_time
            FROM interactions i
            JOIN users u ON i.UserID = u.UserID
            JOIN posts p ON i.PostID = p.PostID
            WHERE i.Type = 'comment'
            ORDER BY i.CreatedAt DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($comments as $comment) {
            $activities[] = $comment;
        }
        
        // Get new followers
        $stmt = $this->db->prepare("
            SELECT 
                u.Username,
                u.ProfilePic,
                'started following you' as action,
                NULL as target_content,
                'follow' as target_type,
                f.CreatedAt as activity_time
            FROM friends f
            JOIN users u ON f.RequesterID = u.UserID
            WHERE f.Status = 'Accepted'
            ORDER BY f.CreatedAt DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $follows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($follows as $follow) {
            $activities[] = $follow;
        }
        
        // Get shares (if you have a shares table)
        // Sort all activities by time
        usort($activities, function($a, $b) {
            return strtotime($b['activity_time']) - strtotime($a['activity_time']);
        });
        
        return array_slice($activities, 0, $limit);
    }

    public function getTimeAgo($datetime) {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;
        
        if ($diff < 60) {
            return $diff . ' seconds ago';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 2592000) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            return date('M j, Y', $time);
        }
    }

    // Top Active Users
    public function getTopActiveUsers($limit = 5) {
        $stmt = $this->db->prepare("
            SELECT 
                u.UserID,
                u.Username,
                u.ProfilePic,
                COUNT(DISTINCT p.PostID) as total_posts,
                COUNT(DISTINCT i.InteractionID) as total_interactions,
                COUNT(DISTINCT s.StoryID) as total_stories
            FROM users u
            LEFT JOIN posts p ON u.UserID = p.UserID AND p.CreatedAt >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            LEFT JOIN interactions i ON u.UserID = i.UserID AND i.CreatedAt >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            LEFT JOIN stories s ON u.UserID = s.UserID AND s.CreatedAt >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY u.UserID
            ORDER BY (total_posts + total_interactions + total_stories) DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>