#!/usr/bin/env python3
import pickle
from collections import Counter
from pathlib import Path

import matplotlib.pyplot as plt


Log = dict[str, object]


def classify_request(log: Log) -> str:
    path = log["path"]
    query = log["query"]
    if query.startswith("action=profile;area=alerts_popup"):
        return "alerts"
    if path == "/cron.php":
        return "cron"
    if "UptimeRobot" in log["userAgent"]:
        return "uptimerobot"
    if path.startswith("/index.php/topic,"):
        return "topic"
    if path.startswith("/index.php/board,"):
        return "board"
    if (path == "/index.php" or path == "/") and not query:
        return "index"
    if path.startswith("/Smileys"):
        return "smiley"
    if path.startswith("/Themes/DUMBDefault/images") \
       or path.startswith("/Themes/default/images"):
        return "theme_images"
    if path.startswith("/Themes/DUMBDefault/css") \
       or path.startswith("/Themes/default/css"):
        return "theme_css"
    if path.startswith("/Themes/DUMBDefault/scripts") \
       or path.startswith("/Themes/default/scripts"):
        return "theme_scripts"
    if path.startswith("/avatars") or path.startswith("/custom_avatar"):
        return "avatar"
    if path.startswith("/assets"):
        return "assets"
    if path.startswith("/shop_items"):
        return "shop_items"
    if path == "/favicon.ico":
        return "favicon"
    if path == "/index.php":
        spl = sum((sq.split('&') for sq in query.split(';')), [])
        parsed_query = {}
        for q in spl:
            parts = q.split('=', 1)
            if len(parts) == 2:
                parsed_query[parts[0]] = parts[1]
            else:
                parsed_query[parts[0]] = ''
        if "action" in parsed_query:
            if parsed_query["action"] == "profile" and "area" in parsed_query:
                return f"profile-{parsed_query["area"]}"
            return parsed_query["action"]
        return "other_index"
    return "other_not_index"


def create_chart(category_counts: Counter[str], output_path: str) -> None:
    threshold = 0.005 * category_counts.total()
    sorted_counts = category_counts.most_common()
    labels = [x[0] for x in sorted_counts if x[1] > threshold] + ['minor']
    values = [x[1] for x in sorted_counts if x[1] > threshold] + \
             [sum(x[1] for x in sorted_counts if x[1] <= threshold)]

    figure, axis = plt.subplots(figsize=(9, 6))
    wedges, _, percentage_texts = axis.pie(
        values,
        autopct="%1.1f%%",
        pctdistance=1.15,
        startangle=90,
    )
    for wedge, percentage_text in zip(wedges, percentage_texts):
        angle = (wedge.theta1 + wedge.theta2) / 2
        if 90 < angle < 270:
            angle += 180
        percentage_text.set_rotation(angle)
        percentage_text.set_rotation_mode("anchor")
    axis.legend(
        wedges,
        labels,
        title="Request categories",
        loc="center left",
        bbox_to_anchor=(1, 0.5),
    )
    axis.axis("equal")
    figure.tight_layout()

    figure.savefig(output_path, dpi=150)
    plt.close(figure)


if __name__ == "__main__":
    logs_dir = Path(__file__).resolve().parent.parent / "logs"
    logs_file = logs_dir / "combined.pkl"
    with logs_file.open("rb") as input_file:
        logs = pickle.load(input_file)
    category_counts = Counter(classify_request(log) for log in logs)
    create_chart(category_counts, "categories_count.png")
