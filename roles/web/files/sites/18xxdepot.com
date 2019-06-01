18xxdepot.com {
    import auth

    proxy / localhost:8080 {
        transparent
    }
}
