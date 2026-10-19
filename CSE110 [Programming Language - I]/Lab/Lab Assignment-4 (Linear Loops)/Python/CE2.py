n = int(input("Enter an Integer: "))
nonNegative = 0
negative = 0

for i in range(1, n+1):
    num = int(input("Enter number " + str(i) + ": "))
    if num >= 0:
        nonNegative += 1
    else:
        negative += 1

print(nonNegative, "Non-Negative Numbers")
print(negative, "Negative Numbers")