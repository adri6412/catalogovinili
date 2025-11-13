FROM node:18-alpine

# Imposta la directory di lavoro
WORKDIR /app

# Copia i file package.json e package-lock.json
COPY package*.json ./

# Installa le dipendenze
RUN npm install --production

# Copia il resto dei file dell'applicazione
COPY . .

# Espone la porta 3000
EXPOSE 3000

# Avvia l'applicazione
CMD ["npm", "start"]

