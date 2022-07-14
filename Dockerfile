ARG IMAGE

FROM $IMAGE

RUN apt-get update && \
	apt-get install -y curl wget procps zip git g++ pkg-config \
	libssl-dev zlib1g-dev libicu-dev libpq-dev && \
	pecl install mongodb-1.7.4 && \
	docker-php-ext-enable mongodb
	
ARG user_developer_uid=1000
RUN useradd -u $user_developer_uid -g www-data -m -s /bin/bash developer
