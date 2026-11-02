length = int(input(("Length: ")))
width = int(input(("Width: ")))

for i in range(width):
    for j in range(1, length+1):
        print(j, end=" ")
    print()