from pathlib import Path
f = Path("C:/xampp/htdocs/LLAPPP/groq-overview.html").read_text(errors="ignore")
needle = 'https://api.groq.com/openai/v1/responses'
idx = f.find(needle)
print(idx)
if idx != -1:
    print(f[idx-400:idx+800])
