USE studenthub;

-- ==========================================
-- PRACTICAL 12: EVENT MANAGEMENT CRUD
-- ==========================================

CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    location VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    status ENUM('Open', 'Closed') NOT NULL DEFAULT 'Open',
    poster VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

-- ==========================================
-- INSERT SAMPLE EVENTS
-- ==========================================

INSERT INTO events
(title, description, event_date, location, category, status, poster)
VALUES
(
    'Web Development Workshop',
    'Learn modern web development using HTML, CSS, JavaScript and PHP.',
    '2026-11-15',
    'CSPIT Computer Lab',
    'Workshop',
    'Open',
    NULL
),
(
    'Annual Technical Fest',
    'A technical festival featuring coding competitions and project exhibitions.',
    '2026-11-20',
    'CSPIT Campus',
    'Technical',
    'Open',
    NULL
),
(
    'Hackathon 2026',
    'Participate in a coding hackathon and develop innovative software solutions.',
    '2026-12-05',
    'CHARUSAT Campus',
    'Competition',
    'Open',
    NULL
),
(
    'Cultural Festival',
    'Enjoy cultural performances, music, dance and student activities.',
    '2026-12-12',
    'University Auditorium',
    'Cultural',
    'Open',
    NULL
),
(
    'Database Management Seminar',
    'Understand relational databases, SQL queries and database security.',
    '2026-10-10',
    'CSPIT Seminar Hall',
    'Seminar',
    'Closed',
    NULL
);

-- ==========================================
-- VERIFY EVENTS
-- ==========================================

SELECT * FROM events ORDER BY event_date ASC;

UPDATE events
SET poster = 'Annual_tech_fest.jpg'
WHERE title = 'Annual Technical Fest';

UPDATE events
SET poster = 'Community_meetup.jpg'
WHERE title = 'Cultural Festival';

UPDATE events
SET poster = 'Workshop_on_ai.jpg'
WHERE title = 'Web Development Workshop';