FROM node:20

ARG WORK_DIR=/frontendStore
ENV PATH ${WORK_DIR}/node_modules/.bin:$PATH

WORKDIR ${WORK_DIR}

COPY package*.json ./

RUN npm install -g @angular/cli
RUN npm install

COPY . .

EXPOSE 4200

CMD ["ng", "serve", "--host", "0.0.0.0"]