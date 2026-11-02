height = int(input("Height: "))

for i in range(1, height + 1):
    for j in range(height - i):
        print(" ", end=" ")

    for k in range(height - i + 1, height + 1):
        print(k, end=" ")
    print()