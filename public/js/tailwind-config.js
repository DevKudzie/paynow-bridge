tailwind.config = {
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                border: "hsl(240 3.7% 15.9%)",
                input: "hsl(240 3.7% 15.9%)",
                ring: "hsl(240 4.9% 83.9%)",
                background: "hsl(240 10% 3.9%)",
                foreground: "hsl(0 0% 98%)",
                primary: {
                    DEFAULT: "hsl(240 5.9% 90%)",
                    foreground: "hsl(240 5.9% 10%)",
                    hover: "hsl(240 4.8% 82.9%)"
                },
                secondary: {
                    DEFAULT: "hsl(240 3.7% 15.9%)",
                    foreground: "hsl(0 0% 98%)",
                },
                destructive: {
                    DEFAULT: "hsl(0 62.8% 30.6%)",
                    foreground: "hsl(0 85.7% 97.3%)",
                },
                muted: {
                    DEFAULT: "hsl(240 3.7% 15.9%)",
                    foreground: "hsl(240 5% 64.9%)",
                },
                accent: {
                    DEFAULT: "hsl(240 3.7% 15.9%)",
                    foreground: "hsl(0 0% 98%)",
                },
                card: {
                    DEFAULT: "hsl(240 10% 3.9%)",
                    foreground: "hsl(0 0% 98%)",
                },
                success: {
                    DEFAULT: "hsl(143, 85%, 30%)",
                    foreground: "hsl(0 0% 98%)",
                },
                light: {
                    background: "hsl(0 0% 97%)",
                    foreground: "hsl(222.2 47.4% 11.2%)",
                    border: "hsl(214.3 31.8% 91.4%)",
                    input: "hsl(214.3 31.8% 91.4%)",
                    card: "hsl(0 0% 100%)",
                    muted: "hsl(210 40% 96.1%)"
                }
            },
            borderRadius: {
                lg: "0.5rem",
                md: "calc(0.5rem - 2px)",
                sm: "calc(0.5rem - 4px)",
            }
        }
    }
} 