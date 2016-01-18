CREATE TABLE run (
    id INTEGER PRIMARY KEY,
    context VARCHAR,
    date DATE
);

CREATE TABLE subject (
    id INTEGER PRIMARY KEY,
    benchmark VARCHAR,
    name VARCHAR
);
CREATE INDEX subject_benchmark_idx ON subject(benchmark);
CREATE INDEX subject_name_idx ON subject(name);

CREATE TABLE variant (
    id INTEGER PRIMARY KEY,
    run_id INTEGER,
    subject_id INTEGER,
    sleep INTEGER,
    output_time_unit VARCHAR(20),
    output_mode VARCHAR(10),
    revolutions INTEGER,
    warmup INTEGER,
    retry_threshold FLOAT,
    FOREIGN KEY (subject_id) REFERENCES subject(id),
    FOREIGN KEY (run_id) REFERENCES run(id)
);

CREATE TABLE variant_parameter (
    variant_id INT,
    parameter_id INT,
    FOREIGN KEY (variant_id) REFERENCES variant(id),
    FOREIGN KEY (parameter_id) REFERENCES parameter(id)
);

CREATE TABLE sgroup (
    id INTEGER PRIMARY KEY,
    name VARCHAR
);
CREATE INDEX sgroup_name_idx ON sgroup(name);
CREATE TABLE sgroup_subject (
    subject_id INTEGER,
    sgroup_id INTEGER,
    FOREIGN KEY (subject_id) REFERENCES subject(id),
    FOREIGN KEY (sgroup_id) REFERENCES sgroup(id)
);

CREATE TABLE environment (
    id INTEGER PRIMARY KEY,
    run_id INT,
    provider VARCHAR,
    key VARCHAR,
    value VARCHAR,
    FOREIGN KEY (run_id) REFERENCES run(id)
);

CREATE TABLE parameter (
    id INTEGER PRIMARY KEY,
    key VARCHAR,
    value VARCHAR
);
CREATE INDEX parameter_key_idx ON parameter(key);
CREATE INDEX parameter_value_idx ON parameter(value);

CREATE TABLE iteration (
    variant_id INTEGER,
    time INTEGER,
    memory INTEGER,
    reject_count INTEGER,
    FOREIGN KEY (variant_id) REFERENCES variant(id)
);

CREATE TABLE version (
    phpbench_version VARCHAR,
    date DATE
);
