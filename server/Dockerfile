# ─── Backend Node.js / Express ───
FROM node:18-alpine

# Installer les dépendances système
RUN apk add --no-cache python3 make g++

# Créer le dossier de travail
WORKDIR /app

# Copier package.json + lockfile
COPY package.json package-lock.json* pnpm-lock.yaml* bun.lockb* ./
 
# Installer les dépendances Node
RUN npm install

# Installer sequelize-cli globalement
RUN npm install -g sequelize-cli

# Installer les dépendances restantes (express-session, etc.)
RUN npm install express-session
RUN npm install jsonwebtoken bcrypt multer passport passport-google-oauth20


# Copier le code source
COPY . .

# Exposer le port sur lequel tourne Express
EXPOSE 3001

# Commande de démarrage
CMD ["node", "server.js"]

