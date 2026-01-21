<?php

header('Content-Type: application/json');

require_once 'config.php';


session_start();


$response = ['status' => 'error', 'message' => 'Invalid action.'];


if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        
        
         case 'getStudentDashboardData':
            $data = [];
            
            $data['live_matches_count'] = $conn->query("SELECT COUNT(*) as count FROM matches WHERE status = 'LIVE'")->fetch_assoc()['count'];
            $data['upcoming_matches_count'] = $conn->query("SELECT COUNT(*) as count FROM matches WHERE status = 'UPCOMING'")->fetch_assoc()['count'];
            
            
            $userId = $_SESSION['user_id'] ?? 0;
            $predResult = $conn->query("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'correct' THEN 1 ELSE 0 END) as correct FROM predictions WHERE user_id = $userId AND status != 'upcoming'");
            $predStats = $predResult->fetch_assoc();
            $data['accuracy'] = ($predStats['total'] > 0) ? round(($predStats['correct'] / $predStats['total']) * 100) : 0;

            
            $liveMatchQuery = "SELECT m.*, t1.name as team1_name, t2.name as team2_name, s.name as sport_name FROM matches m JOIN teams t1 ON m.team1_id = t1.id JOIN teams t2 ON m.team2_id = t2.id JOIN sports s ON m.sport_id = s.id WHERE m.status = 'LIVE' ORDER BY m.datetime DESC LIMIT 1";
            $data['live_match'] = $conn->query($liveMatchQuery)->fetch_assoc();

            
            $upcomingQuery = "SELECT m.*, t1.name as team1_name, t2.name as team2_name, s.name as sport_name FROM matches m JOIN teams t1 ON m.team1_id = t1.id JOIN teams t2 ON m.team2_id = t2.id JOIN sports s ON m.sport_id = s.id WHERE m.status = 'UPCOMING' ORDER BY m.datetime ASC LIMIT 2";
            $upcomingResult = $conn->query($upcomingQuery);
            $data['upcoming_matches'] = [];
            while($row = $upcomingResult->fetch_assoc()) {
                $data['upcoming_matches'][] = $row;
            }

            // top predictor
            $topPredictorQuery = "SELECT u.username, COUNT(p.id) as score 
                                  FROM predictions p
                                  JOIN users u ON p.user_id = u.id
                                  WHERE p.status = 'correct'
                                  GROUP BY p.user_id
                                  ORDER BY score DESC
                                  LIMIT 1";
            $data['top_predictor'] = $conn->query($topPredictorQuery)->fetch_assoc();

            $response['status'] = 'success';
            $response['data'] = $data;
            break;


      
         case 'getAllMatches':
            
            $query = "SELECT 
                        m.*, 
                        t1.name as team1_name, 
                        t2.name as team2_name, 
                        s.name as sport_name,
                        CASE
                            WHEN m.status = 'COMPLETED' THEN
                                CASE
                                    WHEN m.score1 > m.score2 THEN t1.name
                                    WHEN m.score2 > m.score1 THEN t2.name
                                    ELSE 'DRAW'
                                END
                            ELSE NULL
                        END as winning_team_name
                      FROM matches m 
                      JOIN teams t1 ON m.team1_id = t1.id 
                      JOIN teams t2 ON m.team2_id = t2.id 
                      JOIN sports s ON m.sport_id = s.id 
                      ORDER BY m.datetime DESC";
            
            $result = $conn->query($query);
            $matches = [];
            while($row = $result->fetch_assoc()) {
                $matches[] = $row;
            }
            $response['status'] = 'success';
            $response['data'] = $matches;
            break;
       
        case 'getPointsTableData':
           
            $query = "SELECT t.*, s.name as sport_name FROM teams t JOIN sports s ON t.sport_id = s.id ORDER BY s.name, t.points DESC";
            $result = $conn->query($query);
            
            $points_data = [];
            if ($result) {
                while($row = $result->fetch_assoc()) {

                    $points_data[$row['sport_name']][] = $row;
                }
                $response['status'] = 'success';
                $response['data'] = $points_data;
            } else {
                $response['message'] = 'Could not fetch points table data.';
            }
            break;
         case 'submitVolunteerApplication':
            if (isset($_POST['name'], $_POST['email'])) {
                $name = trim($_POST['name']);
                $email = trim($_POST['email']);
                $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

                
                if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $response['message'] = 'Invalid name or email provided.';
                } else {
                  
                    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ? UNION SELECT email FROM volunteer_applications WHERE email = ?");
                    $stmt->bind_param("ss", $email, $email);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        $response['message'] = 'This email is already in use or has a pending application.';
                    } else {

                        $stmt = $conn->prepare("INSERT INTO volunteer_applications (name, email, reason) VALUES (?, ?, ?)");
                        $stmt->bind_param("sss", $name, $email, $reason);
                        
                        if ($stmt->execute()) {
                            $response['status'] = 'success';
                            $response['message'] = 'Application submitted successfully! The admin will review it shortly.';
                        } else {
                            $response['message'] = 'Database error. Could not submit application.';
                        }
                    }
                    $stmt->close();
                }
            } else {
                $response['message'] = 'Please provide your name and email.';
            }
            break;
        case 'getVolunteerApplications':
            $query = "SELECT * FROM volunteer_applications WHERE status = 'pending' ORDER BY created_at ASC";
            $result = $conn->query($query);
            $applications = [];
            if ($result) {
                while($row = $result->fetch_assoc()) {
                    $applications[] = $row;
                }
                $response['status'] = 'success';
                $response['data'] = $applications;
            } else {
                $response['message'] = 'Could not fetch applications.';
            }
            break;

        
        case 'approveVolunteerApplication':
            if (!isset($_POST['id'])) {
                throw new Exception('Application ID not provided.');
            }
            
            $appId = $_POST['id'];
            $conn->begin_transaction();

            try {
               
                $app_stmt = $conn->prepare("SELECT name, email FROM volunteer_applications WHERE id = ? AND status = 'pending'");
                $app_stmt->bind_param("i", $appId);
                $app_stmt->execute();
                $application = $app_stmt->get_result()->fetch_assoc();

                if ($application) {
                    $username = $application['name'];
                    $email = $application['email'];
                    $temporary_password = 'password123'; 
                    $hashed_password = password_hash($temporary_password, PASSWORD_DEFAULT);
                    $role = 'volunteer';

                    
                    $user_check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                    $user_check_stmt->bind_param("s", $email);
                    $user_check_stmt->execute();
                    $existing_user = $user_check_stmt->get_result()->fetch_assoc();

                    if ($existing_user) {
                       
                        $userId = $existing_user['id'];
                        $promote_stmt = $conn->prepare("UPDATE users SET role = ?, password = ?, force_password_change = 1 WHERE id = ?");
                        $promote_stmt->bind_param("ssi", $role, $hashed_password, $userId);
                        $promote_stmt->execute();
                        $response['message'] = "User '{$username}' has been promoted to Volunteer. Their temporary password is: password123";
                    } else {
                        
                        $create_stmt = $conn->prepare("INSERT INTO users (username, email, password, role, force_password_change) VALUES (?, ?, ?, ?, 1)");
                        $create_stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
                        $create_stmt->execute();
                        $response['message'] = "New volunteer account created for '{$username}'. Their temporary password is: password123";
                    }

                    
                    $update_stmt = $conn->prepare("UPDATE volunteer_applications SET status = 'approved' WHERE id = ?");
                    $update_stmt->bind_param("i", $appId);
                    $update_stmt->execute();
                    
                    $conn->commit();
                    $response['status'] = 'success';
                } else {
                    throw new Exception('Application not found or already processed.');
                }
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
            break;

        
        case 'denyVolunteerApplication':
            if (isset($_POST['id'])) {
                $appId = $_POST['id'];
                $stmt = $conn->prepare("UPDATE volunteer_applications SET status = 'denied' WHERE id = ?");
                $stmt->bind_param("i", $appId);
                if ($stmt->execute()) {
                    $response['status'] = 'success';
                    $response['message'] = 'Application has been denied.';
                } else {
                    $response['message'] = 'Could not update application status.';
                }
            } else {
                $response['message'] = 'Application ID not provided.';
            }
            break;

        case 'getDashboardStats':
            $stats = [
                'totalUsers' => 0, 'totalVolunteers' => 0, 'totalMatches' => 0,
                'liveMatches' => 0, 'totalPredictions' => 0, 'pendingApplications' => 0,
            ];

            
            $stats['totalUsers'] = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
            
            $stats['totalVolunteers'] = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'volunteer'")->fetch_assoc()['count'];
            
            $stats['totalMatches'] = $conn->query("SELECT COUNT(*) as count FROM matches")->fetch_assoc()['count'];
            
            $stats['liveMatches'] = $conn->query("SELECT COUNT(*) as count FROM matches WHERE status = 'LIVE'")->fetch_assoc()['count'];
            
            $stats['totalPredictions'] = $conn->query("SELECT COUNT(*) as count FROM predictions")->fetch_assoc()['count'];
            
            $stats['pendingApplications'] = $conn->query("SELECT COUNT(*) as count FROM volunteer_applications WHERE status = 'pending'")->fetch_assoc()['count'];
            
            $response['status'] = 'success';
            $response['data'] = $stats;
            $response['message'] = 'Dashboard stats fetched successfully.';
            break;

        case 'submitVolunteerApplication':
            if (!isset($_POST['name'], $_POST['email'])) {
                throw new Exception('Please provide your name and email.');
            }
            
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

            if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid name or email provided.');
            }

            
            $stmt = $conn->prepare("SELECT email FROM users WHERE email = ? UNION SELECT email FROM volunteer_applications WHERE email = ?");
            $stmt->bind_param("ss", $email, $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                throw new Exception('This email is already in use or has a pending application.');
            }

            
            $stmt = $conn->prepare("INSERT INTO volunteer_applications (name, email, reason) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $reason);
            
            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Application submitted successfully! The admin will review it shortly.';
            } else {
                throw new Exception('Database error. Could not submit application.');
            }
            break;

            case 'updatePassword':
            if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
                throw new Exception('You must be logged in to change your password.');
            }
            if (isset($_POST['new_password'])) {
                $new_password = $_POST['new_password'];
                $userId = $_SESSION['user_id'];

                if (strlen($new_password) < 6) { 
                    throw new Exception('Password must be at least 6 characters long.');
                }

                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

               
                $stmt = $conn->prepare("UPDATE users SET password = ?, force_password_change = 0 WHERE id = ?");
                $stmt->bind_param("si", $hashed_password, $userId);

                if ($stmt->execute()) {
                    $response['status'] = 'success';
                    $response['message'] = 'Password updated successfully!';
                } else {
                    throw new Exception('Failed to update password.');
                }
            } else {
                throw new Exception('New password not provided.');
            }
            break;

        case 'getVolunteerDashboardData':
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'volunteer') {
                throw new Exception('Unauthorized access.');
            }
            $volunteerId = $_SESSION['user_id'];
            $data = [];

           
            $sports_query = "SELECT s.name FROM volunteer_assignments va JOIN sports s ON va.sport_id = s.id WHERE va.volunteer_user_id = ?";
            $stmt = $conn->prepare($sports_query);
            $stmt->bind_param("i", $volunteerId);
            $stmt->execute();
            $result = $stmt->get_result();
            $data['assigned_sports'] = [];
            while($row = $result->fetch_assoc()) {
                $data['assigned_sports'][] = $row['name'];
            }

            
            $stats_query = "SELECT 
                                SUM(CASE WHEN status = 'LIVE' THEN 1 ELSE 0 END) as live_matches,
                                COUNT(id) as total_assigned,
                                SUM(CASE WHEN report_status = 'pending' THEN 1 ELSE 0 END) as pending_reports,
                                SUM(CASE WHEN status = 'COMPLETED' THEN 1 ELSE 0 END) as completed_matches
                           FROM matches WHERE volunteer_id = ?";
            $stmt = $conn->prepare($stats_query);
            $stmt->bind_param("i", $volunteerId);
            $stmt->execute();
            $data['stats'] = $stmt->get_result()->fetch_assoc();

            
            $live_match_query = "SELECT m.*, t1.name as team1_name, t2.name as team2_name 
                               FROM matches m 
                               JOIN teams t1 ON m.team1_id = t1.id 
                               JOIN teams t2 ON m.team2_id = t2.id 
                               WHERE m.volunteer_id = ? AND m.status = 'LIVE' 
                               ORDER BY m.datetime DESC LIMIT 1";
            $stmt = $conn->prepare($live_match_query);
            $stmt->bind_param("i", $volunteerId);
            $stmt->execute();
            $data['live_match_widget'] = $stmt->get_result()->fetch_assoc();

            
            $assigned_matches_query = "SELECT m.*, t1.name as team1_name, t2.name as team2_name 
                                     FROM matches m 
                                     JOIN teams t1 ON m.team1_id = t1.id 
                                     JOIN teams t2 ON m.team2_id = t2.id 
                                     WHERE m.volunteer_id = ? AND m.status != 'LIVE' 
                                     ORDER BY m.datetime DESC LIMIT 2";
            $stmt = $conn->prepare($assigned_matches_query);
            $stmt->bind_param("i", $volunteerId);
            $stmt->execute();
            $result = $stmt->get_result();
            $data['assigned_matches_widget'] = [];
            while($row = $result->fetch_assoc()) {
                $data['assigned_matches_widget'][] = $row;
            }

            $response['status'] = 'success';
            $response['data'] = $data;
            break;

          // Inside your api.php switch statement
		case 'getLiveScoreData':
    $data = [
        'live' => [],
        'upcoming' => [],
        'completed' => [],
        'sports' => [],
        'teams' => []
    ];

    // 1. Get all sports
    $sports_result = $conn->query("SELECT id, name FROM sports ORDER BY name ASC");
    if($sports_result) {
        while($row = $sports_result->fetch_assoc()) {
            $data['sports'][] = $row;
        }
    }

    // 2. FIXED: Removed 'abbreviation' from the query
    $teams_result = $conn->query("SELECT id, name FROM teams ORDER BY name ASC");
    if($teams_result) {
        while($row = $teams_result->fetch_assoc()) {
            $data['teams'][] = $row;
        }
    }

    // 3. Get all matches
    $query = "SELECT m.*, t1.name as team1_name, t2.name as team2_name, s.name as sport_name 
              FROM matches m 
              JOIN teams t1 ON m.team1_id = t1.id 
              JOIN teams t2 ON m.team2_id = t2.id 
              JOIN sports s ON m.sport_id = s.id 
              ORDER BY m.datetime DESC";
    
    $result = $conn->query($query);
    
    if ($result) {
        while($row = $result->fetch_assoc()) {
            if ($row['status'] == 'LIVE') {
                $data['live'][] = $row;
            } elseif ($row['status'] == 'UPCOMING') {
                $data['upcoming'][] = $row;
            } elseif ($row['status'] == 'COMPLETED') {
                $data['completed'][] = $row;
            }
        }
        
        usort($data['upcoming'], function($a, $b) {
            return strtotime($a['datetime']) - strtotime($b['datetime']);
        });

        // Ensure we send a valid JSON response
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'data' => $data]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database Query Failed: ' . $conn->error]);
    }
    exit;
    break;

        case 'getPredictionMatches':
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('User not logged in.');
            }
            $userId = $_SESSION['user_id'];

           
            $query = "SELECT m.*, t1.name as team1_name, t2.name as team2_name, s.name as sport_name, p.predicted_winner_team_id
                      FROM matches m
                      JOIN teams t1 ON m.team1_id = t1.id
                      JOIN teams t2 ON m.team2_id = t2.id
                      JOIN sports s ON m.sport_id = s.id
                      LEFT JOIN predictions p ON m.id = p.match_id AND p.user_id = ?
                      ORDER BY m.datetime ASC";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $matches = [];
            while($row = $result->fetch_assoc()) {
                $matches[] = $row;
            }
            $response['status'] = 'success';
            $response['data'] = $matches;
            break;

        
        case 'makePrediction':
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('You must be logged in to make a prediction.');
            }
            if (!isset($_POST['match_id'], $_POST['predicted_winner_id'])) {
                throw new Exception('Invalid prediction data.');
            }
            
            $userId = $_SESSION['user_id'];
            $matchId = $_POST['match_id'];
            $predictedWinnerId = $_POST['predicted_winner_id'];

            
            $match_stmt = $conn->prepare("SELECT status FROM matches WHERE id = ?");
            $match_stmt->bind_param("i", $matchId);
            $match_stmt->execute();
            $match_result = $match_stmt->get_result()->fetch_assoc();

            if (!$match_result || $match_result['status'] !== 'UPCOMING') {
                throw new Exception('Prediction deadline has passed for this match.');
            }

            // Insert the prediction
            $stmt = $conn->prepare("INSERT INTO predictions (user_id, match_id, predicted_winner_team_id) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $userId, $matchId, $predictedWinnerId);

            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Prediction saved successfully!';
            } else {
                
                throw new Exception('You have already made a prediction for this match.');
            }
            break;
        
         case 'getAllTeamStats':
            if (!isset($_SESSION['user_id'])) { throw new Exception('Unauthorized access.'); }

            // stats-liveScore.php
            $teams_query = "SELECT t.*, s.name as sport_name FROM teams t JOIN sports s ON t.sport_id = s.id ORDER BY t.name ASC";
            $teams_result = $conn->query($teams_query);
            $all_teams_data = [];
            while($row = $teams_result->fetch_assoc()) {
                $all_teams_data[$row['id']] = $row;
            }

            
            foreach ($all_teams_data as $teamId => &$team_data) { // Use reference '&' to modify array directly
                
                if ($team_data['matches_played'] > 0) {
                    $team_data['win_rate'] = round(($team_data['wins'] / $team_data['matches_played']) * 100);
                } else {
                    $team_data['win_rate'] = 0;
                }

                // position
                $sport_id = $team_data['sport_id'];
                $position_query = "SELECT id FROM teams WHERE sport_id = ? ORDER BY points DESC, name ASC";
                $pos_stmt = $conn->prepare($position_query);
                $pos_stmt->bind_param("i", $sport_id);
                $pos_stmt->execute();
                $teams_in_sport = $pos_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $position = array_search($teamId, array_column($teams_in_sport, 'id')) + 1;
                $team_data['position'] = $position;

                // Recent Form
                $form_stmt = $conn->prepare("SELECT team1_id, team2_id, score1, score2 FROM matches WHERE (team1_id = ? OR team2_id = ?) AND status = 'COMPLETED' ORDER BY datetime DESC LIMIT 5");
                $form_stmt->bind_param("ii", $teamId, $teamId);
                $form_stmt->execute();
                $form_result = $form_stmt->get_result();
                $recent_form = [];
                while($match_row = $form_result->fetch_assoc()) {
                    $is_team1 = ($match_row['team1_id'] == $teamId);
                    if ($is_team1) {
                        $recent_form[] = ($match_row['score1'] > $match_row['score2']) ? 'W' : 'L';
                    } else {
                        $recent_form[] = ($match_row['score2'] > $match_row['score1']) ? 'W' : 'L';
                    }
                }
                $team_data['recentForm'] = $recent_form;
            }

            $response['status'] = 'success';
          
            $response['data'] = array_values($all_teams_data);
            break;

         case 'getMyPredictionsData':
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('User not logged in.');
            }
            $userId = $_SESSION['user_id'];
            $data = [
                'stats' => [
                    'total' => 0, 'correct' => 0, 'incorrect' => 0, 'accuracy' => 0,
                    'rank' => 'N/A', 'total_predictors' => 0,
                ],
                'history' => [],
            ];

            //prediction history
            $query = "SELECT 
                        m.datetime, s.name as sport_name, t1.name as team1_name, t2.name as team2_name,
                        (SELECT name FROM teams WHERE id = p.predicted_winner_team_id) as predicted_team_name,
                        CASE
                            WHEN m.status = 'UPCOMING' OR m.status = 'LIVE' THEN 'upcoming'
                            WHEN m.score1 > m.score2 AND p.predicted_winner_team_id = m.team1_id THEN 'correct'
                            WHEN m.score2 > m.score1 AND p.predicted_winner_team_id = m.team2_id THEN 'correct'
                            ELSE 'incorrect'
                        END as status,
                        CASE
                            WHEN m.status = 'COMPLETED' THEN
                                CASE WHEN m.score1 > m.score2 THEN t1.name WHEN m.score2 > m.score1 THEN t2.name ELSE 'Draw' END
                            ELSE 'TBD'
                        END as actual_winner_team_name
                      FROM predictions p
                      JOIN matches m ON p.match_id = m.id
                      JOIN sports s ON m.sport_id = s.id
                      JOIN teams t1 ON m.team1_id = t1.id
                      JOIN teams t2 ON m.team2_id = t2.id
                      WHERE p.user_id = ? ORDER BY m.datetime DESC";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $history = [];
            while($row = $result->fetch_assoc()) { $history[] = $row; }
            $data['history'] = $history;

            
            $total_preds = count($history);
            $completed_preds = array_filter($history, function($p) { return $p['status'] !== 'upcoming'; });
            $correct_preds = array_filter($completed_preds, function($p) { return $p['status'] === 'correct'; });
            $data['stats']['total'] = $total_preds;
            $data['stats']['correct'] = count($correct_preds);
            $data['stats']['incorrect'] = count($completed_preds) - count($correct_preds);
            $data['stats']['accuracy'] = (count($completed_preds) > 0) ? round((count($correct_preds) / count($completed_preds)) * 100) : 0;

            //used in student dashboard for global rank
            $leaderboard_query = "SELECT
                                      p.user_id,
                                      COUNT(p.id) as score
                                  FROM predictions p
                                  JOIN matches m ON p.match_id = m.id
                                  WHERE
                                      (m.status = 'COMPLETED') AND
                                      (
                                        (m.score1 > m.score2 AND p.predicted_winner_team_id = m.team1_id) OR
                                        (m.score2 > m.score1 AND p.predicted_winner_team_id = m.team2_id)
                                      )
                                  GROUP BY p.user_id
                                  ORDER BY score DESC, p.user_id ASC";
            
            $leaderboard_result = $conn->query($leaderboard_query);
            if ($leaderboard_result) {
                $current_rank = 0;
                $data['stats']['total_predictors'] = $leaderboard_result->num_rows;
                while ($row = $leaderboard_result->fetch_assoc()) {
                    $current_rank++;
                    if ($row['user_id'] == $userId) {
                        $data['stats']['rank'] = $current_rank;
                        break; 
                    }
                }
            }
            
            $response['status'] = 'success';
            $response['data'] = $data;
            break;

        
         case 'updateMatchScore':
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'volunteer') {
                throw new Exception('Unauthorized access.');
            }
            if (!isset($_POST['match_id'], $_POST['score1'], $_POST['score2'], $_POST['status'])) {
                throw new Exception('Missing required fields for score update.');
            }
            
            $volunteerId = $_SESSION['user_id'];
            $matchId = $_POST['match_id'];
            $score1 = intval($_POST['score1']);
            $score2 = intval($_POST['score2']);
            $wickets1 = isset($_POST['wickets1']) ? intval($_POST['wickets1']) : null;
            $wickets2 = isset($_POST['wickets2']) ? intval($_POST['wickets2']) : null;
            $commentary = $_POST['commentary'] ?? '';
            $status = $_POST['status']; //live or completed

            $conn->begin_transaction();
            try {
                // check whether volunteer is assigned to match
                $check_stmt = $conn->prepare("SELECT team1_id, team2_id FROM matches WHERE id = ? AND volunteer_id = ?");
                $check_stmt->bind_param("ii", $matchId, $volunteerId);
                $check_stmt->execute();
                $match_data = $check_stmt->get_result()->fetch_assoc();
                if (!$match_data) {
                    throw new Exception('You are not authorized to update this match.');
                }
                //update match details
                $report_status_sql = ($status === 'COMPLETED') ? ", report_status = 'pending'" : "";
                $stmt = $conn->prepare("UPDATE matches SET score1 = ?, score2 = ?, wickets1 = ?, wickets2 = ?, commentary = ?, status = ? $report_status_sql WHERE id = ?");
                $stmt->bind_param("iiiissi", $score1, $score2, $wickets1, $wickets2, $commentary, $status, $matchId);
                $stmt->execute();

                
                if ($status === 'COMPLETED') {
                    $team1Id = $match_data['team1_id'];
                    $team2Id = $match_data['team2_id'];
                    $winnerId = null;
                    $loserId = null;

                    // points table
                    if ($score1 > $score2) {
                        $winnerId = $team1Id;
                        $loserId = $team2Id;
                        $conn->query("UPDATE teams SET matches_played = matches_played + 1, wins = wins + 1, points = points + 2 WHERE id = $winnerId");
                        $conn->query("UPDATE teams SET matches_played = matches_played + 1, losses = losses + 1 WHERE id = $loserId");
                    } elseif ($score2 > $score1) {
                        $winnerId = $team2Id;
                        $loserId = $team1Id;
                        $conn->query("UPDATE teams SET matches_played = matches_played + 1, wins = wins + 1, points = points + 2 WHERE id = $winnerId");
                        $conn->query("UPDATE teams SET matches_played = matches_played + 1, losses = losses + 1 WHERE id = $loserId");
                    } else { // Handle a draw
                        $conn->query("UPDATE teams SET matches_played = matches_played + 1, points = points + 1 WHERE id = $team1Id OR id = $team2Id");
                    }

                    // winrate calculation
                    $conn->query("UPDATE teams SET win_rate = ROUND((wins / matches_played) * 100) WHERE id = $team1Id");
                    $conn->query("UPDATE teams SET win_rate = ROUND((wins / matches_played) * 100) WHERE id = $team2Id");

                    // check the student prediction if its correct
                    if ($winnerId) {
                        $grade_stmt = $conn->prepare("UPDATE predictions SET status = CASE WHEN predicted_winner_team_id = ? THEN 'correct' ELSE 'incorrect' END WHERE match_id = ? AND status = 'upcoming'");
                        $grade_stmt->bind_param("ii", $winnerId, $matchId);
                        $grade_stmt->execute();
                    } 
                    // If it is draw all predictions are incorrect
                    else { 
                        $grade_stmt = $conn->prepare("UPDATE predictions SET status = 'incorrect' WHERE match_id = ? AND status = 'upcoming'");
                        $grade_stmt->bind_param("i", $matchId);
                        $grade_stmt->execute();
                    }
                }

                $conn->commit();
                $response['status'] = 'success';
                $response['message'] = 'Score updated successfully! All stats have been adjusted.';

            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
            break;

        
        case 'getVolunteerReportData':
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'volunteer') {
                throw new Exception('Unauthorized access.');
            }
            $volunteerId = $_SESSION['user_id'];
            $data = ['pending' => [], 'submitted' => []];
            $query = "SELECT m.id, m.reportSummary, t1.name as team1_name, t2.name as team2_name, m.datetime, s.name as sport_name, m.report_status
                      FROM matches m
                      JOIN teams t1 ON m.team1_id = t1.id
                      JOIN teams t2 ON m.team2_id = t2.id
                      JOIN sports s ON m.sport_id = s.id
                      WHERE m.volunteer_id = ? AND m.status = 'COMPLETED'
                      ORDER BY m.datetime DESC";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $volunteerId);
            $stmt->execute();
            $result = $stmt->get_result();
            while($row = $result->fetch_assoc()) {
                if ($row['report_status'] === 'pending') {
                    $data['pending'][] = $row;
                } else {
                    $data['submitted'][] = $row;
                }
            }
            $response['status'] = 'success';
            $response['data'] = $data;
            break;

        
        case 'submitMatchReport':
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'volunteer') {
                throw new Exception('Unauthorized access.');
            }
            if (!isset($_POST['match_id'], $_POST['summary'])) {
                throw new Exception('Missing required report data.');
            }
            $volunteerId = $_SESSION['user_id'];
            $matchId = $_POST['match_id'];
            $summary = trim($_POST['summary']);

            $check_stmt = $conn->prepare("SELECT id FROM matches WHERE id = ? AND volunteer_id = ? AND report_status = 'pending'");
            $check_stmt->bind_param("ii", $matchId, $volunteerId);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows === 0) {
                throw new Exception('You cannot submit a report for this match.');
            }

            $stmt = $conn->prepare("UPDATE matches SET report_status = 'submitted', reportSummary = ? WHERE id = ?");
            $stmt->bind_param("si", $summary, $matchId);
            if($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Match report submitted successfully!';
            } else {
                throw new Exception('Database error: Could not submit report.');
            }
            break;
       
        case 'getVolunteerDashboardData':
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'volunteer') {
                throw new Exception('Unauthorized access.');
            }
            $volunteerId = $_SESSION['user_id'];
            $data = [];

            // gt volunteer assigned sports
            $sports_query = "SELECT s.name FROM volunteer_assignments va JOIN sports s ON va.sport_id = s.id WHERE va.volunteer_user_id = ?";
            $stmt = $conn->prepare($sports_query);
            $stmt->bind_param("i", $volunteerId);
            $stmt->execute();
            $result = $stmt->get_result();
            $data['assigned_sports'] = [];
            while($row = $result->fetch_assoc()) {
                $data['assigned_sports'][] = $row['name'];
            }

            
            $stats_query = "SELECT 
                                SUM(CASE WHEN status = 'LIVE' THEN 1 ELSE 0 END) as live_matches,
                                COUNT(id) as total_assigned,
                                SUM(CASE WHEN report_status = 'pending' THEN 1 ELSE 0 END) as pending_reports,
                                SUM(CASE WHEN status = 'COMPLETED' THEN 1 ELSE 0 END) as completed_matches
                           FROM matches WHERE volunteer_id = ?";
            $stmt = $conn->prepare($stats_query);
            $stmt->bind_param("i", $volunteerId);
            $stmt->execute();
            $data['stats'] = $stmt->get_result()->fetch_assoc();

            // Get live match-flex in volunteer dashboard
            $live_match_query = "SELECT m.*, t1.name as team1_name, t2.name as team2_name, s.name as sport_name 
                               FROM matches m 
                               JOIN teams t1 ON m.team1_id = t1.id 
                               JOIN teams t2 ON m.team2_id = t2.id 
                               JOIN sports s ON m.sport_id = s.id
                               WHERE m.volunteer_id = ? AND m.status = 'LIVE' 
                               ORDER BY m.datetime DESC LIMIT 1";
            $stmt = $conn->prepare($live_match_query);
            $stmt->bind_param("i", $volunteerId);
            $stmt->execute();
            $data['live_match_widget'] = $stmt->get_result()->fetch_assoc();

            
            $assigned_matches_query = "SELECT m.*, t1.name as team1_name, t2.name as team2_name, s.name as sport_name 
                                     FROM matches m 
                                     JOIN teams t1 ON m.team1_id = t1.id 
                                     JOIN teams t2 ON m.team2_id = t2.id 
                                     JOIN sports s ON m.sport_id = s.id
                                     WHERE m.volunteer_id = ? AND m.status != 'LIVE' 
                                     ORDER BY m.datetime DESC LIMIT 2";
            $stmt = $conn->prepare($assigned_matches_query);
            $stmt->bind_param("i", $volunteerId);
            $stmt->execute();
            $result = $stmt->get_result();
            $data['assigned_matches_widget'] = [];
            while($row = $result->fetch_assoc()) {
                $data['assigned_matches_widget'][] = $row;
            }

            $response['status'] = 'success';
            $response['data'] = $data;
            break;

        case 'getVolunteerLiveMatches':
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'volunteer') {
                throw new Exception('Unauthorized access.');
            }
            $volunteerId = $_SESSION['user_id'];
            $query = "SELECT m.id, t1.name as team1_name, t2.name as team2_name, m.score1, m.score2, m.wickets1, m.wickets2, m.commentary, s.name as sport_name
                      FROM matches m
                      JOIN teams t1 ON m.team1_id = t1.id
                      JOIN teams t2 ON m.team2_id = t2.id
                      JOIN sports s ON m.sport_id = s.id
                      WHERE m.volunteer_id = ? AND m.status = 'LIVE'";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $volunteerId);
            $stmt->execute();
            $result = $stmt->get_result();
            $matches = [];
            while($row = $result->fetch_assoc()) {
                $matches[] = $row;
            }
            $response['status'] = 'success';
            $response['data'] = $matches;
            break;
    
         
        case 'getVolunteerMatches':
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'volunteer') {
                throw new Exception('Unauthorized access.');
            }
            $volunteerId = $_SESSION['user_id'];
            $query = "SELECT m.id, t1.name as team1_name, t2.name as team2_name, m.datetime, m.venue, m.status, s.name as sport_name
                      FROM matches m
                      JOIN teams t1 ON m.team1_id = t1.id
                      JOIN teams t2 ON m.team2_id = t2.id
                      JOIN sports s ON m.sport_id = s.id
                      WHERE m.volunteer_id = ?
                      ORDER BY m.datetime DESC";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $volunteerId);
            $stmt->execute();
            $result = $stmt->get_result();
            $matches = [];
            while($row = $result->fetch_assoc()) {
                $matches[] = $row;
            }
            $response['status'] = 'success';
            $response['data'] = $matches;
            break;

        //if time is up start match automatically
        case 'startMatch':
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'volunteer') {
                throw new Exception('Unauthorized access.');
            }
            if (!isset($_POST['match_id'])) {
                throw new Exception('Match ID not provided.');
            }

            $volunteerId = $_SESSION['user_id'];
            $matchId = $_POST['match_id'];

            $check_stmt = $conn->prepare("SELECT id FROM matches WHERE id = ? AND volunteer_id = ? AND status = 'UPCOMING'");
            $check_stmt->bind_param("ii", $matchId, $volunteerId);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows === 0) {
                throw new Exception('You are not authorized to start this match, or it is not an upcoming match.');
            }

            // Update match live
            $stmt = $conn->prepare("UPDATE matches SET status = 'LIVE' WHERE id = ?");
            $stmt->bind_param("i", $matchId);
            
            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Match has been started successfully!';
            } else {
                throw new Exception('Failed to start the match in the database.');
            }
            break;
            
        case 'getVolunteerPrerequisites':
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'volunteer') {
                throw new Exception('Unauthorized access.');
            }
            $volunteerId = $_SESSION['user_id'];
            $data = [
                'assigned_sports' => [],
                'all_teams' => []
            ];

            // Get assigned sports
            $sports_query = "SELECT s.id, s.name FROM volunteer_assignments va JOIN sports s ON va.sport_id = s.id WHERE va.volunteer_user_id = ?";
            $stmt = $conn->prepare($sports_query);
            $stmt->bind_param("i", $volunteerId);
            $stmt->execute();
            $result = $stmt->get_result();
            while($row = $result->fetch_assoc()) {
                $data['assigned_sports'][] = $row;
            }

            
            $teams_query = "SELECT id, name, sport_id FROM teams ORDER BY name ASC";
            $result = $conn->query($teams_query);
            while($row = $result->fetch_assoc()) {
                $data['all_teams'][] = $row;
            }

            $response['status'] = 'success';
            $response['data'] = $data;
            break;


        // schedule match-volunteer
        case 'scheduleVolunteerMatch':
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'volunteer') {
                throw new Exception('Unauthorized access.');
            }
            if (!isset($_POST['sport_id'], $_POST['team1_id'], $_POST['team2_id'], $_POST['datetime'], $_POST['venue'])) {
                throw new Exception('All fields are required.');
            }

            $volunteerId = $_SESSION['user_id'];
            $sportId = $_POST['sport_id'];
            
            
            $check_stmt = $conn->prepare("SELECT * FROM volunteer_assignments WHERE volunteer_user_id = ? AND sport_id = ?");
            $check_stmt->bind_param("ii", $volunteerId, $sportId);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows === 0) {
                throw new Exception('You are not authorized to schedule matches for this sport.');
            }

            // new match
            $stmt = $conn->prepare("INSERT INTO matches (team1_id, team2_id, sport_id, volunteer_id, datetime, venue, status) VALUES (?, ?, ?, ?, ?, ?, 'UPCOMING')");
            $stmt->bind_param("iiiiss", $_POST['team1_id'], $_POST['team2_id'], $sportId, $volunteerId, $_POST['datetime'], $_POST['venue']);
            
            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Match scheduled successfully!';
            } else {
                throw new Exception('Database error: Could not schedule match.');
            }
            break;

        case 'getSubmittedReports':
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
                throw new Exception('Unauthorized access.');
            }
            $query = "SELECT 
                        m.id, 
                        m.datetime,
                        m.reportSummary,
                        t1.name as team1_name, 
                        t2.name as team2_name, 
                        s.name as sport_name,
                        u.username as volunteer_name
                      FROM matches m
                      JOIN teams t1 ON m.team1_id = t1.id
                      JOIN teams t2 ON m.team2_id = t2.id
                      JOIN sports s ON m.sport_id = s.id
                      LEFT JOIN users u ON m.volunteer_id = u.id
                      WHERE m.report_status = 'submitted'
                      ORDER BY m.datetime DESC";
            
            $result = $conn->query($query);
            $reports = [];
            if ($result) {
                while($row = $result->fetch_assoc()) {
                    $reports[] = $row;
                }
                $response['status'] = 'success';
                $response['data'] = $reports;
            } else {
                throw new Exception('Could not fetch submitted reports from the database.');
            }
            break;
            

         case 'getAnalyticsData':
            $data = [
                'teams' => [],
                'matches' => [],
            ];

            
            $teams_query = "SELECT t.id, t.name, t.win_rate, s.name as sport_name FROM teams t JOIN sports s ON t.sport_id = s.id ORDER BY t.name ASC";
            $teams_result = $conn->query($teams_query);
            while($row = $teams_result->fetch_assoc()) {
                $data['teams'][] = $row;
            }

            $matches_query = "SELECT m.status, s.name as sport_name FROM matches m JOIN sports s ON m.sport_id = s.id";
            $matches_result = $conn->query($matches_query);
            while($row = $matches_result->fetch_assoc()) {
                $data['matches'][] = $row;
            }

            $response['status'] = 'success';
            $response['data'] = $data;
            break;
        case 'getMatchCreationData':
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
                throw new Exception('Unauthorized access.');
            }
            $data = [
                'sports' => [],
                'teams' => [],
                'volunteers' => []
            ];

            // Get all sports
            $sports_result = $conn->query("SELECT id, name FROM sports ORDER BY name ASC");
            while($row = $sports_result->fetch_assoc()) {
                $data['sports'][] = $row;
            }

            // Get all teams
            $teams_result = $conn->query("SELECT id, name, sport_id FROM teams ORDER BY name ASC");
            while($row = $teams_result->fetch_assoc()) {
                $data['teams'][] = $row;
            }

            // Get all volunteers
            $volunteers_result = $conn->query("SELECT id, username FROM users WHERE role = 'volunteer' ORDER BY username ASC");
            while($row = $volunteers_result->fetch_assoc()) {
                $data['volunteers'][] = $row;
            }
            
            $response['status'] = 'success';
            $response['data'] = $data;
            break;

       
        case 'createMatch':
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
                throw new Exception('Unauthorized access.');
            }
            if (!isset($_POST['sport_id'], $_POST['team1_id'], $_POST['team2_id'], $_POST['datetime'], $_POST['venue'])) {
                throw new Exception('All fields are required.');
            }
            if ($_POST['team1_id'] === $_POST['team2_id']) {
                throw new Exception('Team 1 and Team 2 cannot be the same.');
            }

            $volunteerId = !empty($_POST['volunteer_id']) ? $_POST['volunteer_id'] : null;

            $stmt = $conn->prepare("INSERT INTO matches (sport_id, team1_id, team2_id, datetime, venue, volunteer_id, status) VALUES (?, ?, ?, ?, ?, ?, 'UPCOMING')");
            $stmt->bind_param("iiissi", $_POST['sport_id'], $_POST['team1_id'], $_POST['team2_id'], $_POST['datetime'], $_POST['venue'], $volunteerId);
            
            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Match created successfully!';
            } else {
                throw new Exception('Database error: Could not create the match.');
            }
            break;

        case 'getTeamsAndSports':
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
                throw new Exception('Unauthorized access.');
            }
            $data = [
                'sports' => [],
                'teams' => [],
            ];
            // Get all sports
            $sports_result = $conn->query("SELECT id, name FROM sports ORDER BY name ASC");
            while($row = $sports_result->fetch_assoc()) {
                $data['sports'][] = $row;
            }
            // Get all teams
            $teams_query = "SELECT t.id, t.name, t.sport_id, s.name as sport_name FROM teams t JOIN sports s ON t.sport_id = s.id ORDER BY t.name ASC";
            $teams_result = $conn->query($teams_query);
            while($row = $teams_result->fetch_assoc()) {
                $data['teams'][] = $row;
            }
            $response['status'] = 'success';
            $response['data'] = $data;
            break;

      
        case 'addTeam':
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
                throw new Exception('Unauthorized access.');
            }
            if (!isset($_POST['name'], $_POST['sport_id'])) {
                throw new Exception('Missing required fields.');
            }
            $stmt = $conn->prepare("INSERT INTO teams (name, sport_id) VALUES (?, ?)");
            $stmt->bind_param("si", $_POST['name'], $_POST['sport_id']);
            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Team added successfully!';
            } else {
                throw new Exception('Database error: Could not add team.');
            }
            break;

        
        case 'updateTeam':
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
                throw new Exception('Unauthorized access.');
            }
            if (!isset($_POST['id'], $_POST['name'], $_POST['sport_id'])) {
                throw new Exception('Missing required fields.');
            }
            $stmt = $conn->prepare("UPDATE teams SET name = ?, sport_id = ? WHERE id = ?");
            $stmt->bind_param("sii", $_POST['name'], $_POST['sport_id'], $_POST['id']);
            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Team updated successfully!';
            } else {
                throw new Exception('Database error: Could not update team.');
            }
            break;

        
        case 'deleteTeam':
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
                throw new Exception('Unauthorized access.');
            }
            if (!isset($_POST['id'])) {
                throw new Exception('Team ID not provided.');
            }
            $stmt = $conn->prepare("DELETE FROM teams WHERE id = ?");
            $stmt->bind_param("i", $_POST['id']);
            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Team deleted successfully.';
            } else {
                
                throw new Exception('Database error: This team cannot be deleted because it is part of a scheduled match.');
            }
            break;

         case 'getVolunteersAndSports':
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { throw new Exception('Unauthorized access.'); }
            $data = ['sports' => [], 'volunteers' => []];
            $sports_result = $conn->query("SELECT id, name FROM sports ORDER BY name ASC");
            while($row = $sports_result->fetch_assoc()) { $data['sports'][] = $row; }
            $volunteer_query = "SELECT u.id, u.username, u.email, GROUP_CONCAT(s.name SEPARATOR ', ') as assigned_sports, GROUP_CONCAT(s.id SEPARATOR ',') as assigned_sports_ids FROM users u LEFT JOIN volunteer_assignments va ON u.id = va.volunteer_user_id LEFT JOIN sports s ON va.sport_id = s.id WHERE u.role = 'volunteer' GROUP BY u.id ORDER BY u.username ASC";
            $volunteer_result = $conn->query($volunteer_query);
            while($row = $volunteer_result->fetch_assoc()) {
                $row['assigned_sports'] = $row['assigned_sports'] ? explode(', ', $row['assigned_sports']) : [];
                $row['assigned_sports_ids'] = $row['assigned_sports_ids'] ? array_map('intval', explode(',', $row['assigned_sports_ids'])) : [];
                $data['volunteers'][] = $row;
            }
            $response['status'] = 'success';
            $response['data'] = $data;
            break;

        case 'updateVolunteerAssignments':
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { throw new Exception('Unauthorized access.'); }
            if (!isset($_POST['volunteer_id'], $_POST['sports_ids'])) { throw new Exception('Missing required data.'); }
            $volunteerId = $_POST['volunteer_id'];
            $sportsIds = json_decode($_POST['sports_ids']);
            if (!is_array($sportsIds)) { throw new Exception('Invalid sports data format.'); }
            $conn->begin_transaction();
            try {
                $delete_stmt = $conn->prepare("DELETE FROM volunteer_assignments WHERE volunteer_user_id = ?");
                $delete_stmt->bind_param("i", $volunteerId);
                $delete_stmt->execute();
                if (!empty($sportsIds)) {
                    $insert_stmt = $conn->prepare("INSERT INTO volunteer_assignments (volunteer_user_id, sport_id) VALUES (?, ?)");
                    foreach ($sportsIds as $sportId) {
                        $safeSportId = intval($sportId);
                        $insert_stmt->bind_param("ii", $volunteerId, $safeSportId);
                        $insert_stmt->execute();
                    }
                }
                $conn->commit();
                $response['status'] = 'success';
                $response['message'] = 'Volunteer assignments updated successfully!';
            } catch (Exception $e) {
                $conn->rollback();
                throw new Exception('Database transaction failed: ' . $e->getMessage());
            }
            break;

        case 'deleteVolunteer':
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { throw new Exception('Unauthorized access.'); }
            if (!isset($_POST['volunteer_id'])) { throw new Exception('Volunteer ID not provided.'); }
            $volunteerId = $_POST['volunteer_id'];
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'volunteer'");
            $stmt->bind_param("i", $volunteerId);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $response['status'] = 'success';
                    $response['message'] = 'Volunteer deleted successfully.';
                } else {
                    throw new Exception('Volunteer not found or could not be deleted.');
                }
            } else {
                throw new Exception('Database error during deletion.');
            }
            break;
        
        case 'getUsers':
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
                throw new Exception('Unauthorized access.');
            }
            $users = [];
            $result = $conn->query("SELECT id, username, email, role FROM users ORDER BY username ASC");
            while($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
            $response['status'] = 'success';
            $response['data'] = $users;
            break;

        
        case 'addUser':
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
                throw new Exception('Unauthorized access.');
            }
            if (!isset($_POST['username'], $_POST['email'], $_POST['password'], $_POST['role'])) {
                throw new Exception('All fields are required to add a new user.');
            }
            $username = $_POST['username'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $role = $_POST['role'];
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'User added successfully!';
            } else {
                throw new Exception('Database error: Username or email may already exist.');
            }
            break;

       
        case 'updateUser':
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
                throw new Exception('Unauthorized access.');
            }
            if (!isset($_POST['id'], $_POST['role'])) {
                throw new Exception('Missing user ID or role.');
            }
            $userId = $_POST['id'];
            $role = $_POST['role'];
            
            
            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET role = ?, password = ? WHERE id = ?");
                $stmt->bind_param("ssi", $role, $password, $userId);
            } else {
                $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->bind_param("si", $role, $userId);
            }

            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'User updated successfully!';
            } else {
                throw new Exception('Database error: Could not update user.');
            }
            break;

        
        case 'deleteUser':
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
                throw new Exception('Unauthorized access.');
            }
            if (!isset($_POST['id'])) {
                throw new Exception('User ID not provided.');
            }
            // avoid admin from deleting him/her
            if ($_POST['id'] == $_SESSION['user_id']) {
                throw new Exception('You cannot delete your own account.');
            }
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $_POST['id']);
            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'User deleted successfully.';
            } else {
                throw new Exception('Database error: Could not delete user.');
            }
            break;

        case 'getSystemSettings':
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
                throw new Exception('Unauthorized access.');
            }
            $settings = [];
            $result = $conn->query("SELECT setting_key, setting_value FROM settings");
            while($row = $result->fetch_assoc()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            $response['status'] = 'success';
            $response['data'] = $settings;
            break;

       
        case 'updateSystemSettings':
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
                throw new Exception('Unauthorized access.');
            }
            if (!isset($_POST['settings'])) {
                throw new Exception('No settings data provided.');
            }
            
            $settings = json_decode($_POST['settings'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid settings format.');
            }

            $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            foreach ($settings as $key => $value) {
                
                $db_value = $value ? '1' : '0';
                $stmt->bind_param("ss", $db_value, $key);
                $stmt->execute();
            }
            
            $response['status'] = 'success';
            $response['message'] = 'System settings updated successfully!';
            break;

         case 'getVolunteerReportData':
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'volunteer') {
                throw new Exception('Unauthorized access.');
            }
            $volunteerId = $_SESSION['user_id'];
            $data = [
                'pending' => [],
                'submitted' => []
            ];
            $query = "SELECT m.id, t1.name as team1_name, t2.name as team2_name, m.datetime, s.name as sport_name, m.report_status
                      FROM matches m
                      JOIN teams t1 ON m.team1_id = t1.id
                      JOIN teams t2 ON m.team2_id = t2.id
                      JOIN sports s ON m.sport_id = s.id
                      WHERE m.volunteer_id = ? AND m.status = 'COMPLETED'
                      ORDER BY m.datetime DESC";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $volunteerId);
            $stmt->execute();
            $result = $stmt->get_result();
            while($row = $result->fetch_assoc()) {
                if ($row['report_status'] === 'pending') {
                    $data['pending'][] = $row;
                } else {
                    $data['submitted'][] = $row;
                }
            }
            $response['status'] = 'success';
            $response['data'] = $data;
            break;

       
        case 'submitMatchReport':
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'volunteer') {
                throw new Exception('Unauthorized access.');
            }
            if (!isset($_POST['match_id'], $_POST['summary'])) {
                throw new Exception('Missing required report data.');
            }
            $volunteerId = $_SESSION['user_id'];
            $matchId = $_POST['match_id'];
            $summary = trim($_POST['summary']);

            
            $check_stmt = $conn->prepare("SELECT id FROM matches WHERE id = ? AND volunteer_id = ? AND report_status = 'pending'");
            $check_stmt->bind_param("ii", $matchId, $volunteerId);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows === 0) {
                throw new Exception('You cannot submit a report for this match.');
            }

            $stmt = $conn->prepare("UPDATE matches SET report_status = 'submitted', reportSummary = ? WHERE id = ?");
            $stmt->bind_param("si", $summary, $matchId);
            if($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Match report submitted successfully!';
            } else {
                throw new Exception('Database error: Could not submit report.');
            }
            break;

        case 'getStudentDashboardData':
            $data = [];
            // Get counts
            $data['live_matches_count'] = $conn->query("SELECT COUNT(*) as count FROM matches WHERE status = 'LIVE'")->fetch_assoc()['count'];
            $data['upcoming_matches_count'] = $conn->query("SELECT COUNT(*) as count FROM matches WHERE status = 'UPCOMING'")->fetch_assoc()['count'];
            
            
            $userId = $_SESSION['user_id'] ?? 0;
            $predResult = $conn->query("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'correct' THEN 1 ELSE 0 END) as correct FROM predictions WHERE user_id = $userId AND status != 'upcoming'");
            $predStats = $predResult->fetch_assoc();
            $data['accuracy'] = ($predStats['total'] > 0) ? round(($predStats['correct'] / $predStats['total']) * 100) : 0;

            
            $liveMatchQuery = "SELECT m.*, t1.name as team1_name, t2.name as team2_name, s.name as sport_name FROM matches m JOIN teams t1 ON m.team1_id = t1.id JOIN teams t2 ON m.team2_id = t2.id JOIN sports s ON m.sport_id = s.id WHERE m.status = 'LIVE' ORDER BY m.datetime DESC LIMIT 1";
            $data['live_match'] = $conn->query($liveMatchQuery)->fetch_assoc();

            
            $upcomingQuery = "SELECT m.*, t1.name as team1_name, t2.name as team2_name, s.name as sport_name FROM matches m JOIN teams t1 ON m.team1_id = t1.id JOIN teams t2 ON m.team2_id = t2.id JOIN sports s ON m.sport_id = s.id WHERE m.status = 'UPCOMING' ORDER BY m.datetime ASC LIMIT 2";
            $upcomingResult = $conn->query($upcomingQuery);
            $data['upcoming_matches'] = [];
            while($row = $upcomingResult->fetch_assoc()) {
                $data['upcoming_matches'][] = $row;
            }

           
            $topTeamQuery = "SELECT name, wins FROM teams ORDER BY wins DESC LIMIT 1";
            $data['top_team'] = $conn->query($topTeamQuery)->fetch_assoc();

            $response['status'] = 'success';
            $response['data'] = $data;
            break;

         case 'getChampionshipStandings':
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('Unauthorized access.');
            }
            $data = [
                'leaderboard' => [],
                'champion' => null,
            ];

        
            $query = "SELECT
                        name as branch_name,
                        COUNT(*) as championships_won
                      FROM (
                          SELECT t.*
                          FROM teams t
                          JOIN (
                              SELECT sport_id, MAX(points) as max_points
                              FROM teams
                              GROUP BY sport_id
                          ) as max_scores ON t.sport_id = max_scores.sport_id AND t.points = max_scores.max_points
                      ) as winners
                      GROUP BY branch_name
                      ORDER BY championships_won DESC, branch_name ASC";
            
            $result = $conn->query($query);
            if ($result) {
                while($row = $result->fetch_assoc()) {
                    $data['leaderboard'][] = $row;
                }
                if (!empty($data['leaderboard'])) {
                    $data['champion'] = $data['leaderboard'][0]; // The first one in the sorted list is the champion
                }
            } else {
                throw new Exception('Could not calculate championship standings.');
            }
            
            $response['status'] = 'success';
            $response['data'] = $data;
            break;

         
        default:
            $response['message'] = "Action '{$action}' not recognized.";
            break;
    }
}
function getTeamNameById($conn, $teamId) {
    $stmt = $conn->prepare("SELECT name FROM teams WHERE id = ?");
    $stmt->bind_param("i", $teamId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result ? $result['name'] : 'N/A';
}

$conn->close();


echo json_encode($response);
?>
