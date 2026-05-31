FROM node:20-bookworm-slim

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --prefer-offline

COPY . .

EXPOSE 5173

# Dev server inside the image — no host folder mount (avoids WSL2 file-watch overload).
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0", "--port", "5173"]
