n = int(input("Input n = "))
print("Output: ")

for i in range(0, n+1):
    if i % 5 == 0 and i % 3 != 0:
        print(i)