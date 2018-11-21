CREATE TABLE IF NOT EXISTS history (
    fingerprint TEXT PRIMARY KEY
);

CREATE TABLE IF NOT EXISTS queue (
    fingerprint TEXT PRIMARY KEY,
    data TEXT
);