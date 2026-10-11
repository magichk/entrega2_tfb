FROM alpine:3.20
RUN adduser -D -u 1000 appuser
COPY app.sh /app.sh
RUN chmod +x /app.sh
USER appuser
CMD ["/app.sh"]
