<?php
/**
 * app/models/Profile.php
 * Handles database operations for role-specific profile tables.
 */

class Profile {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Get Seeker Profile by User ID
     */
    public function getSeeker($user_id) {
        $sql = "SELECT * FROM seeker_profiles WHERE user_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $profile = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $profile;
    }

    /**
     * Create or Update Seeker Profile
     */
    public function saveSeeker($user_id, $data) {
        $existing = $this->getSeeker($user_id);
        
        if ($existing) {
            $sql = "UPDATE seeker_profiles SET headline=?, summary=?, skills=?, years_experience=?, education_level=?, expected_salary=?, preferred_location=?, resume_path=? WHERE user_id=?";
            $stmt = mysqli_prepare($this->db, $sql);
            mysqli_stmt_bind_param($stmt, "sssisdssi", 
                $data['headline'], $data['summary'], $data['skills'], 
                $data['years_experience'], $data['education_level'], 
                $data['expected_salary'], 
                $data['preferred_location'], $data['resume_path'], $user_id
            );
        } else {
            $sql = "INSERT INTO seeker_profiles (user_id, headline, summary, skills, years_experience, education_level, expected_salary, preferred_location, resume_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($this->db, $sql);
            mysqli_stmt_bind_param($stmt, "isssisdss", 
                $user_id, $data['headline'], $data['summary'], $data['skills'], 
                $data['years_experience'], $data['education_level'], 
                $data['expected_salary'], $data['preferred_location'], $data['resume_path']
            );
        }
        
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    }

    /**
     * Get Employer Profile by User ID
     */
    public function getEmployer($user_id) {
        $sql = "SELECT * FROM employer_profiles WHERE user_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $profile = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $profile;
    }

    /**
     * Create or Update Employer Profile
     */
    public function saveEmployer($user_id, $data) {
        $existing = $this->getEmployer($user_id);
        
        if ($existing) {
            $sql = "UPDATE employer_profiles SET company_name=?, industry=?, company_size=?, description=?, website=?, address=?, logo_path=? WHERE user_id=?";
            $stmt = mysqli_prepare($this->db, $sql);
            mysqli_stmt_bind_param($stmt, "sssssssi", 
                $data['company_name'], $data['industry'], $data['company_size'], 
                $data['description'], $data['website'], $data['address'], 
                $data['logo_path'], $user_id
            );
        } else {
            $sql = "INSERT INTO employer_profiles (user_id, company_name, industry, company_size, description, website, address, logo_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($this->db, $sql);
            mysqli_stmt_bind_param($stmt, "isssssss", 
                $user_id, $data['company_name'], $data['industry'], $data['company_size'], 
                $data['description'], $data['website'], $data['address'], $data['logo_path']
            );
        }
        
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    }

    /**
     * Get Recruiter Profile by User ID
     */
    public function getRecruiter($user_id) {
        $sql = "SELECT * FROM recruiter_profiles WHERE user_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $profile = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $profile;
    }

    /**
     * Create or Update Recruiter Profile
     */
    public function saveRecruiter($user_id, $data) {
        $existing = $this->getRecruiter($user_id);
        
        if ($existing) {
            $sql = "UPDATE recruiter_profiles SET agency_name=?, specialization=?, description=?, website=? WHERE user_id=?";
            $stmt = mysqli_prepare($this->db, $sql);
            mysqli_stmt_bind_param($stmt, "ssssi", 
                $data['agency_name'], $data['specialization'], 
                $data['description'], $data['website'], $user_id
            );
        } else {
            $sql = "INSERT INTO recruiter_profiles (user_id, agency_name, specialization, description, website) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($this->db, $sql);
            mysqli_stmt_bind_param($stmt, "issss", 
                $user_id, $data['agency_name'], $data['specialization'], 
                $data['description'], $data['website']
            );
        }
        
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    }
}
?>