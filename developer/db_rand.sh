#!/bin/bash

MYSQL_COMMAND="/opt/lampp/bin/mysql Pagani_585281 -uroot"
N_THREADS=60
N_COMMENTS=110

#lorem -p 2

for (( i = 0; i < $N_THREADS; i++ )); do
  USER_IP=$(printf "127.20.20.%d\n" "$((RANDOM % 124 + 1))")

  # Pesco una sezione a caso
  SECTION=$($MYSQL_COMMAND -N -e "SELECT name FROM Section ORDER BY RAND() LIMIT 1")

  echo -e "$i \t Inserisco un thread da $USER_IP nella sezione $SECTION"

  # Inserisco un nuovo thread
  THREAD_ID=$($MYSQL_COMMAND -N -e "INSERT INTO Thread(\`section\`, \`title\`, \`content\`) VALUES ('$SECTION' , '$(lorem -w 5)', '$(lorem -p $((RANDOM % 6 + 1)))'); SELECT LAST_INSERT_ID();" )

  # Aggiungo l'autore
  $MYSQL_COMMAND -e "INSERT INTO Poster(\`inet address\`, \`thread\`, \`is op\`) VALUES (inet6_aton('$USER_IP'), $THREAD_ID, TRUE)"

  for (( j = 0; j < N_COMMENTS; j++ )); do
    # Chi sono?
    USER_IP=$(printf "127.20.20.%d\n" "$((RANDOM % 124 + 1))")

    $MYSQL_COMMAND -e "INSERT INTO Poster(\`inet address\`, \`thread\`) VALUES (inet6_aton('$USER_IP'), $THREAD_ID)" &> /dev/null

    # A chi rispondo ?
    COMMENT_ROOT=$((RANDOM % 6))
    if (( j == 0 || COMMENT_ROOT==0)); then
      REPLIES="NULL"
    else
      REPLIES="\"$($MYSQL_COMMAND -N -e "SELECT id FROM Reply WHERE thread = $THREAD_ID ORDER BY RAND() LIMIT 1")\""
    fi

    # Rispondo!
    $MYSQL_COMMAND -e "
      INSERT INTO Reply(\`thread\`,\`content\`, \`author\`, \`replies\`)
      SELECT
          P.\`thread\`,
          '$(lorem -p $((RANDOM % 3 + 1)))' AS \`content\`,
          P.\`anon id\`,
          $REPLIES AS \`replies\`
      FROM \`Poster\` P
          WHERE P.\`inet address\` = inet6_aton('$USER_IP') AND P.\`thread\` = $THREAD_ID
    "

  done
done