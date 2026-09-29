#!/usr/bin/env python3
import ipaddress
import multiprocessing
import pickle
import re
from datetime import datetime
from pathlib import Path
from urllib.parse import urlsplit


LOG_PATTERN = re.compile(
    r'^(?P<ip>\S+) \S+ \S+ \[(?P<timestamp>[^]]+)\] '
    r'"(?P<method>\S+) (?P<target>\S+) \S+" '
    r'(?P<status>\d{3}) (?P<size>\S+) '
    r'"(?P<referrer>[^"]*)" "(?P<user_agent>[^"]*)"\s*$'
)
Log = dict[str, object]


def parse_line(line: str) -> Log:
    match = LOG_PATTERN.match(line.rstrip("\r\n"))
    if match is None:
        raise ValueError("failed to parse line")

    target = urlsplit(match["target"])
    size = match["size"]
    timestamp = datetime.strptime(
        match["timestamp"], "%d/%b/%Y:%H:%M:%S %z"
    ).timestamp()

    return {
        "ip": ipaddress.ip_address(match["ip"]).packed,
        "timestamp": int(timestamp),
        "method": match["method"],
        "path": target.path,
        "query": target.query,
        "status": int(match["status"]),
        "size": 0 if size == "-" else int(size),
        "referrer": match["referrer"],
        "userAgent": match["user_agent"],
    }


def convert_file(input_path: Path) -> list[Log]:
    print("Converting", input_path)
    with input_path.open("r", encoding="utf-8") as log_file:
        return [parse_line(line) for line in log_file if line.strip()]


if __name__ == "__main__":
    logs_dir = Path(__file__).resolve().parent.parent / "logs"
    input_paths = sorted(logs_dir.glob("access_log." + "[0-9]" * 8))
    with multiprocessing.Pool(processes=multiprocessing.cpu_count()) as pool:
        logs = pool.map(convert_file, input_paths)

    print("Writing")
    with open(logs_dir / "combined.pkl", "wb") as combined:
        pickle.dump(sum(logs, []), combined)
