if [[ "$(docker images -q  silicon-phpunit:latest 2> /dev/null)" == "" ]]; then
  docker build -t silicon-phpunit .
fi

docker run -it --mount src="$(pwd)",target=/usr/src/silicon,type=bind --rm --name silicon-phpunit-run silicon-phpunit php $1