login.18xxdepot.com {
    /headers {
        import auth
    }

    proxy / localhost:8081
}
