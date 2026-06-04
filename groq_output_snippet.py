from pathlib import Path
f = Path("C:/xampp/htdocs/LLAPPP/groq-overview.html").read_text(errors="ignore")
needle = 'output_text'
idx = f.find(needle)
print(idx)
if idx != -1:
    print(f[idx-200:idx+200])
