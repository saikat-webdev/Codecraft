from pathlib import Path
import re
f = Path("C:/xampp/htdocs/LLAPPP/groq-overview.html").read_text(errors="ignore")
for token in ['/models/','Authorization','Bearer','input','prompt','messages']:
    idx = f.find(token)
    if idx != -1:
        print("---", token)
        start = max(0, idx-200)
        end = min(len(f), idx+200)
        print(f[start:end])

# also search for path strings explicitly
matches = set(re.findall(r'"(/[^\\"]*models[^\\"]*)"', f))
print("===")
for m in sorted(matches):
    print(m)
